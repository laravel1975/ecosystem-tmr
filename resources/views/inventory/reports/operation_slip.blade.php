@extends('inventory.reports.layout')

@section('title', 'PACKING LIST')

@section('content')
    <table class="info-table">
        <tr>
            <td class="info-label">Source Document:</td>
            <td>{{ $transfer->source_document ?? '-' }}</td>
            <td class="info-label">From Location:</td>
            <td>{{ $transfer->sourceLocation->name }}</td>
        </tr>
        <tr>
            <td class="info-label">Partner:</td>
            <td>{{ $transfer->contact->name ?? '-' }}</td>
            <td class="info-label">To Location:</td>
            <td>{{ $transfer->destinationLocation->name }}</td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th width="45%">Product</th>
                <th width="15%">From</th>
                <th width="15%" class="qty">Demand</th>
                <th width="20%" align="center">Check</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transfer->moves as $index => $move)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $move->item->name }}</strong><br>
                    <span style="font-size: 10px; color: #666;">Code: {{ $move->item->id }}</span>
                </td>
                <td>{{ $transfer->sourceLocation->name }}</td>
                <td class="qty">
                    <span style="font-size: 16px; font-weight: bold;">{{ number_format($move->quantity_demand) }}</span>
                    {{ $move->item->uom->symbol }}
                </td>
                <td align="center">
                    <div style="border: 1px solid #999; height: 20px; width: 20px; display: inline-block;"></div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 20px;">
        <strong>Note:</strong> {{ $transfer->note ?? '-' }}
    </div>

    <table class="signature-area">
        <tr>
            <td width="33%" align="center">
                <div class="sign-line"></div>
                <div>Picked By</div>
            </td>
            <td width="33%" align="center">
                <div class="sign-line"></div>
                <div>Checked By</div>
            </td>
            <td width="33%"></td>
        </tr>
    </table>
@endsection
