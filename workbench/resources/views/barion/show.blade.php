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
                    <h2 style="margin-top:16px">3DS token fizetés lezárása (Payment/Complete)</h2>
                    <p class="hint" style="margin-top:0">Tokenes fizetésnél, ha a startPayment() <code>ThreeDSAuthenticationRequired</code> recurrence resultot adott, és a vásárló elvégezte a 3D Secure azonosítást. Más fizetésnél a Barion hibát ad.</p>
                    <form method="post" action="{{ route('workbench.barion.complete', $paymentId) }}">@csrf<button>Complete</button></form>
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
