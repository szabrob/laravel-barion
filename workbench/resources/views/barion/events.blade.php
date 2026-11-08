@if ($events === [])
    <p class="muted">Még nem érkezett event. A package callback route (<code>{{ route(\LaravelBarion\LaravelBarion\LaravelBarion::CALLBACK_ROUTE) }}</code>) minden hívásra lekérdezi a PaymentState-et és ide naplóz.</p>
@else
    <div class="scroll"><table>
        <tr><th>Idő</th><th>Event</th><th>PaymentId</th><th>Ellenőrzött státusz</th><th>Összeg</th></tr>
        @foreach ($events as $event)
            <tr>
                <td>{{ $event['at'] }}</td>
                <td>{{ $event['event'] }}</td>
                <td><a href="{{ route('workbench.barion.show', $event['paymentId']) }}"><code>{{ $event['paymentId'] }}</code></a></td>
                <td>@include('barion.status-badge', ['status' => $event['status']])</td>
                <td>{{ $event['total'] }} {{ $event['currency'] }}</td>
            </tr>
        @endforeach
    </table></div>
@endif
