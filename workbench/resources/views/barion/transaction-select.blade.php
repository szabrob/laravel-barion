<label>Tranzakció</label>
<select name="transaction_id">
    @foreach ($transactions as $transaction)
        <option value="{{ $transaction->transactionId }}">{{ $transaction->posTransactionId }} – {{ $transaction->total }} {{ $transaction->currency?->value }}</option>
    @endforeach
</select>
