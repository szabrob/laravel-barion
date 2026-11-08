@extends('barion.layout')

@section('content')
    <div class="grid">
        <section class="card">
            <h2>Konfiguráció</h2>
            @if (isset($status['error']))
                <p class="bad">{{ $status['error'] }}</p>
            @else
                <dl>
                    <dt>Környezet</dt><dd><span class="badge {{ $status['environment'] === 'production' ? 'bad' : 'ok' }}">{{ $status['environment'] }}</span></dd>
                    <dt>API</dt><dd><code>{{ $status['apiUrl'] }}</code></dd>
                    <dt>Gateway</dt><dd><code>{{ $status['gatewayUrl'] }}</code></dd>
                    <dt>POS kulcs</dt><dd>@if ($status['posKey'])<code>{{ $status['posKey'] }}</code>@else<span class="bad">nincs beállítva</span>@endif</dd>
                    <dt>Payee</dt><dd>@if ($status['payee']){{ $status['payee'] }}@else<span class="bad">nincs beállítva</span>@endif</dd>
                    <dt>RedirectUrl</dt><dd><code>{{ $status['redirectUrl'] }}</code></dd>
                    <dt>CallbackUrl</dt><dd>@if ($status['callbackUrl'])<code>{{ $status['callbackUrl'] }}</code>@else<span class="muted">kikapcsolva</span>@endif</dd>
                    <dt>Pixel</dt><dd>{{ $status['pixelId'] ?: '–' }}</dd>
                </dl>
                @if ($status['callbackUrl'] && preg_match('#^https?://(localhost|127\.0\.0\.1)#', $status['callbackUrl']))
                    <p class="hint warn">A CallbackUrl helyi cím, ezt a Barion nem éri el. Valódi callbackhez állíts be egy publikus tunnelt (pl. <code>ngrok http 8000</code>) és írd az APP_URL-be. Addig a fizetés oldalán a „Callback szimuláció” gombbal hívhatod meg ugyanazt a kezelőt.</p>
                @endif
            @endif
        </section>

        <section class="card">
            <h2>Új fizetés indítása</h2>
            <form method="post" action="{{ route('workbench.barion.start') }}">
                @csrf
                <label>Termék neve</label>
                <input name="item_name" value="{{ old('item_name', 'Teszt termék') }}" required>
                <div class="row">
                    <div><label>Mennyiség</label><input name="quantity" type="number" step="0.01" value="{{ old('quantity', 1) }}" required></div>
                    <div><label>Egységár</label><input name="unit_price" type="number" step="0.01" value="{{ old('unit_price', 1000) }}" required></div>
                </div>
                <div class="row">
                    <div><label>Pénznem</label>
                        <select name="currency">@foreach (\LaravelBarion\LaravelBarion\Enums\Currency::cases() as $case)<option @selected(old('currency', 'HUF') === $case->value)>{{ $case->value }}</option>@endforeach</select></div>
                    <div><label>Nyelv</label>
                        <select name="locale">@foreach (\LaravelBarion\LaravelBarion\Enums\Locale::cases() as $case)<option value="{{ $case->value }}" @selected(old('locale', 'hu-HU') === $case->value)>{{ $case->value }}</option>@endforeach</select></div>
                </div>
                <div class="row">
                    <div><label>Fizetés típusa</label>
                        <select name="payment_type">@foreach (\LaravelBarion\LaravelBarion\Enums\PaymentType::cases() as $case)<option @selected(old('payment_type', 'Immediate') === $case->value)>{{ $case->value }}</option>@endforeach</select></div>
                    <div><label>Foglalási / capture időszak</label>
                        <input name="period" value="{{ old('period') }}" placeholder="1.00:00:00"></div>
                </div>
                <label>Fizetési módok</label>
                <select name="funding_sources[]" multiple>
                    @foreach (\LaravelBarion\LaravelBarion\Enums\FundingSource::cases() as $case)
                        <option value="{{ $case->value }}" @selected(in_array($case->value, old('funding_sources', ['All']), true))>{{ $case->value }}</option>
                    @endforeach
                </select>
                <label>Payer hint (e-mail, opcionális)</label>
                <input name="payer_hint" type="email" value="{{ old('payer_hint') }}">
                <label>Saját idempotency key (opcionális, üresen a csomag generálja)</label>
                <input name="idempotency_key" value="{{ old('idempotency_key') }}">
                <input type="hidden" name="guest_checkout" value="0">
                <label class="check"><input type="checkbox" name="guest_checkout" value="1" @checked(old('guest_checkout', '1') === '1')> Vendég fizetés engedélyezése</label>
                <button>Fizetés indítása → Barion</button>
                <p class="hint">Teszt környezetben a Barion dokumentációjában szereplő tesztkártyákkal fizethetsz.</p>
            </form>
        </section>
    </div>

    <section class="card">
        <h2>Indított fizetések</h2>
        @if ($payments === [])
            <p class="muted">Még nincs indított fizetés.</p>
        @else
            <div class="scroll"><table>
                <tr><th>Idő</th><th>PaymentId</th><th>RequestId</th><th>Típus</th><th>Összeg</th><th>Környezet</th><th></th></tr>
                @foreach ($payments as $payment)
                    <tr>
                        <td>{{ $payment['at'] }}</td>
                        <td><a href="{{ route('workbench.barion.show', $payment['paymentId']) }}"><code>{{ $payment['paymentId'] }}</code></a></td>
                        <td><code>{{ $payment['paymentRequestId'] }}</code></td>
                        <td>{{ $payment['type'] }}</td>
                        <td>{{ $payment['total'] }} {{ $payment['currency'] }}</td>
                        <td>{{ $payment['environment'] }}</td>
                        <td><a href="{{ $payment['gatewayUrl'] }}">fizetőoldal</a></td>
                    </tr>
                @endforeach
            </table></div>
        @endif
    </section>

    <section class="card">
        <h2>Beérkezett callback eventek</h2>
        @include('barion.events', ['events' => $events])
        <form method="post" action="{{ route('workbench.barion.clear') }}">@csrf<button class="secondary">Napló törlése</button></form>
    </section>
@endsection
