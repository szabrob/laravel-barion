@if ($schedules === [])
    <p class="muted">Nincs ütemezett token terhelés.</p>
@else
    <div class="scroll"><table>
        <tr><th>Ütemezés</th><th>RecurrenceId</th><th>Típus</th><th>Összeg</th><th>Gyakoriság</th><th>Következő</th><th>Terhelések</th><th>Utolsó eredmény</th><th></th></tr>
        @foreach ($schedules as $schedule)
            <tr>
                <td><code>{{ $schedule['id'] }}</code><br><a href="{{ route('workbench.barion.show', $schedule['sourcePaymentId']) }}" class="muted">regisztráló fizetés</a></td>
                <td><code>{{ $schedule['recurrenceId'] }}</code></td>
                <td>{{ $schedule['recurrenceType'] }}</td>
                <td>{{ $schedule['amount'] }} {{ $schedule['currency'] }}</td>
                <td>{{ $schedule['intervalMinutes'] }} percenként</td>
                <td>{{ $schedule['nextChargeAt'] }}</td>
                <td>{{ $schedule['chargeCount'] }}</td>
                <td>
                    {{ $schedule['lastResult'] ?? '–' }}
                    @if ($schedule['lastPaymentId'])<br><a href="{{ route('workbench.barion.show', $schedule['lastPaymentId']) }}"><code>{{ $schedule['lastPaymentId'] }}</code></a>@endif
                </td>
                <td>
                    <form method="post" action="{{ route('workbench.barion.schedules.charge', $schedule['id']) }}">@csrf<button>Terhelés most</button></form>
                    <form method="post" action="{{ route('workbench.barion.schedules.delete', $schedule['id']) }}">@csrf<button class="danger">Törlés</button></form>
                </td>
            </tr>
        @endforeach
    </table></div>
@endif
