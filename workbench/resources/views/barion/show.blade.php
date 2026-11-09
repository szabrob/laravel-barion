@extends('barion.layout')

@section('title', "Fizetés {$paymentId}")

@section('content')
    <section class="card">
        <h1>Fizetés <code>{{ $paymentId }}</code></h1>
        @if ($stateError)
            <pre class="bad" style="margin-top:12px">{{ $stateError }}</pre>
        @endif
    </section>

    @if ($state)
        @php
            $shopTransactions = array_values(array_filter($state->transactions, fn ($t) => $t->posTransactionId !== null && $t->transactionId !== null));
        @endphp
        <div class="grid">
            <section class="card">
                <h2>PaymentState (v4)</h2>
                <dl>
                    <dt>Státusz</dt><dd>@include('barion.status-badge', ['status' => $state->status])</dd>
                    <dt>Típus</dt><dd>{{ $state->paymentType?->value }}</dd>
                    <dt>Összeg</dt><dd>{{ $state->total }} {{ $state->currency?->value }}</dd>
                    <dt>RequestId</dt><dd><code>{{ $state->paymentRequestId }}</code></dd>
                    <dt>Shop</dt><dd>{{ $state->posName }}</dd>
                    <dt>Fizetési mód</dt><dd>{{ $state->paymentMethod ?? $state->fundingSource ?? '–' }}
                        @if ($state->fundingInformation?->bankCard) ({{ $state->fundingInformation->bankCard->bankCardType }} •••• {{ $state->fundingInformation->bankCard->maskedPan }}) @endif</dd>
                    <dt>Létrehozva</dt><dd>{{ $state->createdAt?->toDateTimeString() }} UTC</dd>
                    <dt>Lezárva</dt><dd>{{ $state->completedAt?->toDateTimeString() ?? '–' }}</dd>
                    <dt>Érvényes</dt><dd>{{ $state->validUntil?->toDateTimeString() ?? '–' }}</dd>
                    @if ($state->reservedUntil)<dt>Foglalva eddig</dt><dd>{{ $state->reservedUntil->toDateTimeString() }}</dd>@endif
                    @if ($state->delayedCaptureUntil)<dt>Capture határidő</dt><dd>{{ $state->delayedCaptureUntil->toDateTimeString() }}</dd>@endif
                </dl>
                @if (in_array($state->status->value, ['Prepared', 'Started'], true))
                    <a class="btn" href="{{ $gatewayUrl }}">Tovább a fizetőoldalra</a>
                @endif
                <a class="btn" href="{{ route('workbench.barion.show', $paymentId) }}" style="background:var(--muted)">Frissítés</a>
                @if (in_array($state->status->value, ['Prepared', 'Started', 'InProgress', 'Waiting'], true))
                    @php $threeDSAuthClientData = $payment['threeDSAuthClientData'] ?? null; @endphp
                    <h2 style="margin-top:16px">3DS token fizetés lezárása (Payment/Complete)</h2>
                    @if ($threeDSAuthClientData && $offsiteGwBase)
                        <p class="hint" style="margin-top:0">A terhelés <code>ThreeDSAuthenticationRequired</code> választ adott. Az azonosítás itt, a workbench oldalán fut a Barion off-site könyvtárával (<code>barion.offsitegw.min.js</code>), siker esetén a Complete automatikusan elindul. A Barion tesztkártyák mindig frictionless folyamatot adnak, challenge csak valódi kártyával jelenik meg.</p>
                        <button type="button" id="offsite-3ds">3DS azonosítás → Complete</button>
                        <p class="hint" id="offsite-3ds-status"></p>
                    @else
                        <p class="hint" style="margin-top:0">Tokenes fizetésnél, ha a startPayment() <code>ThreeDSAuthenticationRequired</code> recurrence resultot adott, és a vásárló elvégezte a 3D Secure azonosítást. Azonosítás nélkül a Barion „The 3DS authentication is not completed” hibát ad.</p>
                    @endif
                    <form method="post" action="{{ route('workbench.barion.complete', $paymentId) }}" id="complete-form">@csrf<button>Complete azonosítás nélkül</button></form>

                    @if ($threeDSAuthClientData && $offsiteGwBase)
                        <link href="{{ $offsiteGwBase }}/static/barion.offsitegw.min.css" rel="stylesheet">
                        <script src="{{ $offsiteGwBase }}/static/barion.offsitegw.min.js"></script>
                        <script>
                            (function () {
                                const status = (text) => { document.getElementById('offsite-3ds-status').textContent = text; };
                                const gw = new Barion.OffsiteGw({
                                    onAuthenticationSucceeded: function () {
                                        status('Sikeres 3DS azonosítás, Payment/Complete indul…');
                                        document.getElementById('complete-form').submit();
                                    },
                                    onAuthenticationFailed: function () {
                                        status('Sikertelen 3DS azonosítás. Nem történt terhelés, a payment még érvényes lehet.');
                                    },
                                });
                                document.addEventListener('DOMContentLoaded', function () { gw.init(); });
                                document.getElementById('offsite-3ds').addEventListener('click', function () {
                                    status('Azonosítás folyamatban…');
                                    gw.authenticate(@json($threeDSAuthClientData));
                                });
                            })();
                        </script>
                    @endif
                @endif
            </section>

            <section class="card">
                <h2>Callback</h2>
                <p class="hint" style="margin-top:0">Ugyanazt futtatja, mint a package callback route: <code>handleCallback($paymentId)</code> → PaymentState lekérdezés → eventek.</p>
                <form method="post" action="{{ route('workbench.barion.callback', $paymentId) }}">@csrf<button>Callback szimuláció</button></form>
                <h2 style="margin-top:16px">Eventek ehhez a fizetéshez</h2>
                @include('barion.events', ['events' => $events])
            </section>
        </div>

        @if ($state->recurrenceType || ! empty($payment['recurrenceId']))
            <section class="card">
                <h2>Tokenes fizetés</h2>
                <dl>
                    <dt>RecurrenceType</dt><dd>{{ $state->recurrenceType?->value ?? $payment['recurrenceType'] ?? '–' }}</dd>
                    <dt>RecurrenceId</dt><dd><code>{{ $payment['recurrenceId'] ?? '–' }}</code> <span class="muted">a workbench naplóból, a PaymentState nem adja vissza</span></dd>
                    <dt>TraceId</dt><dd><code>{{ $state->traceId ?? '–' }}</code></dd>
                </dl>
                @if (! empty($payment['recurrenceId']))
                    @if ($state->status->isSuccessful())
                        <h2 style="margin-top:16px">Ütemezett terhelés erre a kártyára</h2>
                        <form method="post" action="{{ route('workbench.barion.schedule', $paymentId) }}">
                            @csrf
                            <div class="row">
                                <div><label>Összeg</label><input name="amount" type="number" step="0.01" value="{{ old('amount', $state->total ?? 1000) }}" required></div>
                                <div><label>Gyakoriság (perc)</label><input name="interval_minutes" type="number" value="{{ old('interval_minutes', 5) }}" required></div>
                                <div><label>Első terhelés (perc múlva)</label><input name="first_in_minutes" type="number" value="{{ old('first_in_minutes', 1) }}" required></div>
                            </div>
                            <button>Ütemezés mentése</button>
                            <p class="hint">Minden terhelés: <code>startPayment()</code> <code>initiateRecurrence: false</code>, a tárolt recurrenceId, recurrenceType és traceId, terhelésenként új paymentRequestId. Átirányítás nincs.</p>
                        </form>
                    @else
                        <p class="hint">Ütemezett terhelést a kártyaregisztráló fizetés sikeres lezárása után lehet beállítani.</p>
                    @endif
                    @if ($schedules !== [])
                        <h2 style="margin-top:16px">Ütemezések</h2>
                        @include('barion.schedules', ['schedules' => $schedules])
                    @endif
                @endif
            </section>
        @endif

        <section class="card">
            <h2>Tranzakciók</h2>
            <div class="scroll"><table>
                <tr><th>TransactionId</th><th>POSTransactionId</th><th>Típus</th><th>Státusz</th><th>Összeg</th><th>Idő</th><th>Payee</th></tr>
                @foreach ($state->transactions as $transaction)
                    <tr>
                        <td><code>{{ $transaction->transactionId }}</code></td>
                        <td><code>{{ $transaction->posTransactionId }}</code></td>
                        <td>{{ $transaction->transactionType }}</td>
                        <td>@include('barion.status-badge', ['status' => $transaction->status])</td>
                        <td>{{ $transaction->total }} {{ $transaction->currency?->value }}</td>
                        <td>{{ $transaction->transactionTime?->toDateTimeString() }}</td>
                        <td>{{ $transaction->payee?->email }}</td>
                    </tr>
                @endforeach
            </table></div>
        </section>

        @if ($shopTransactions !== [])
            <div class="grid">
                <section class="card">
                    <h2>Visszatérítés (Refund)</h2>
                    <form method="post" action="{{ route('workbench.barion.refund', $paymentId) }}">
                        @csrf
                        @include('barion.transaction-select', ['transactions' => $shopTransactions])
                        <label>Összeg</label>
                        <input name="amount" type="number" step="0.01" value="{{ $shopTransactions[0]->total }}" required>
                        <label>Megjegyzés</label>
                        <input name="comment" value="Workbench refund">
                        <button>Visszatérítés</button>
                        <p class="hint">Minden refund egyedi POSTransactionId-t kap, így külön idempotency key-jel megy.</p>
                    </form>
                </section>

                @if ($state->paymentType?->value === 'Reservation')
                    <section class="card">
                        <h2>Foglalás lezárása (FinishReservation)</h2>
                        <form method="post" action="{{ route('workbench.barion.finish', $paymentId) }}">
                            @csrf
                            @include('barion.transaction-select', ['transactions' => $shopTransactions])
                            <label>Végleges összeg (0 = teljes feloldás)</label>
                            <input name="total" type="number" step="0.01" value="{{ $shopTransactions[0]->total }}" required>
                            <button>Lezárás</button>
                        </form>
                    </section>
                @endif

                @if ($state->paymentType?->value === 'DelayedCapture')
                    <section class="card">
                        <h2>Terhelés (Capture)</h2>
                        <form method="post" action="{{ route('workbench.barion.capture', $paymentId) }}">
                            @csrf
                            @include('barion.transaction-select', ['transactions' => $shopTransactions])
                            <label>Terhelendő összeg</label>
                            <input name="total" type="number" step="0.01" value="{{ $shopTransactions[0]->total }}" required>
                            <button>Capture</button>
                        </form>
                        <form method="post" action="{{ route('workbench.barion.cancel', $paymentId) }}">
                            @csrf
                            <button class="danger">Authorization törlése</button>
                        </form>
                    </section>
                @endif
            </div>
        @endif

        <section class="card">
            <h2>Nyers PaymentStateData</h2>
            <pre>{{ json_encode($state->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        </section>
    @endif
@endsection
