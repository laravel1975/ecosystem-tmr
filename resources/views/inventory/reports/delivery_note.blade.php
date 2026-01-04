@extends('inventory.reports.layout')

@section('title', 'DELIVERY NOTE')

@section('content')
    <table class="info-table" style="border: 1px solid #ddd; padding: 10px;">
        <tr>
            <td width="50%" valign="top">
                <strong>Customer (Bill To):</strong><br>
                {{ $transfer->contact->name ?? 'Walk-in Customer' }}<br>
                {!! nl2br(e($transfer->contact->address ?? '')) !!}<br>
                Tax ID: {{ $transfer->contact->tax_id ?? '-' }}
            </td>
            <td width="50%" valign="top" style="border-left: 1px solid #ddd; padding-left: 10px;">
                <strong>Ship To:</strong><br>
                Same as billing address<br>
                <br>
                <strong>Reference:</strong> {{ $transfer->source_document }}
            </td>
        </tr>
    </table>

    <br>

    <table class="items-table">
        <thead>
            <tr>
                <th width="10%">No.</th>
                <th width="50%">Description</th>
                <th width="20%" class="qty">Quantity</th>
                <th width="20%" class="qty">Unit</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfer->moves as $index => $move)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $move->item->name }}</strong>
                </td>
                <td class="qty">
                    {{ number_format($move->quantity_done > 0 ? $move->quantity_done : $move->quantity_demand) }}
                </td>
                <td class="qty">{{ $move->item->uom->symbol }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px; font-size: 12px; color: #555;">
        * Please check the goods upon delivery. We are not responsible for any damage or missing items after the delivery is accepted.
    </div>

    <table class="signature-area" style="margin-top: 80px;">
        <tr>
            <td width="40%" align="center">
                <div class="sign-line"></div>
                <div>Authorized Signature</div>
                <div style="font-size: 10px;">(TMR Ecosystem)</div>
            </td>
            <td width="20%"></td>
            <td width="40%" align="center">
                <div class="sign-line"></div>
                <div>Received By / Date</div>
                <div style="font-size: 10px;">(Customer Signature)</div>
            </td>
        </tr>
    </table>
@endsection
