<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ticket Noordenlicht - {{ $ticket->activity->title }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }

        body {
            /* Updated font as requested */
            font-family: 'Georgia', sans-serif;
            color: #000000;
            margin: 0;
            padding: 0;
        }

        /* Using background-image is the robust standard for 'cover' behavior
           in PDFs. It prevents stretching and handles centering automatically.
        */
        .header-image-container {
            width: 100%;
            height: 250px;
            background-color: #5a7123;
            background-size: cover;
            background-position: center center;
            background-repeat: no-repeat;
        }

        .content-wrapper {
            padding: 40px;
        }

        .ticket-header {
            border-bottom: 2px solid #5a7123;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: table;
            width: 100%;
        }

        .event-title {
            font-size: 32px;
            font-weight: bold;
            color: #624b25; /* Updated color */
            margin-bottom: 10px;
            display: table-cell;
            vertical-align: bottom;
            width: 70%;
        }

        .event-date-box {
            display: table-cell;
            vertical-align: bottom;
            text-align: right;
            width: 30%;
        }

        .date-large {
            font-size: 24px;
            font-weight: bold;
            color: #5a7123;
        }

        .date-small {
            font-size: 14px;
            color: #5a7123; /* Updated color */
            margin-top: 5px;
        }

        .details-grid {
            display: table;
            width: 100%;
            margin-bottom: 40px;
        }

        .details-col {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            margin-bottom: 5px;
        }

        .value {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 20px;
        }

        .footer {
            position: fixed;
            bottom: 40px;
            left: 40px;
            right: 40px;
            border-top: 1px solid #eee;
            padding-top: 20px;
            text-align: center;
        }

        .footer small {
            display: block;
            margin-bottom: 5px;
            color: #999;
        }

        .barcode-container {
            text-align: center;
            margin-top: 30px;
            padding: 20px;
            background: #f9f9f9;
            border: 1px dashed #ccc;
            border-radius: 8px;
        }

        .barcode-img {
            height: 60px;
            margin-bottom: 10px;
        }

        .ticket-id {
            font-family: 'Courier New', monospace;
            font-size: 14px;
            letter-spacing: 2px;
            color: #555;
        }
    </style>
</head>
<body>

<!-- Header Image with Background-Size Cover -->
@php
    $bgImage = null;
    if ($ticket->activity->image) {
        $path = public_path('files/agenda/agenda_images/' . $ticket->activity->image);
        // Ensure forward slashes for CSS compatibility on Windows
        $bgImage = str_replace('\\', '/', $path);
    }
@endphp

    <!-- We use inline style for the dynamic image URL -->
<div class="header-image-container"
     style="@if($bgImage) background-image: url('{{ $bgImage }}'); @endif">
</div>

<div class="content-wrapper">

    <!-- Title and Date Header -->
    <div class="ticket-header">
        <div class="event-title">
            {{ $ticket->activity->title }}
        </div>
        <div class="event-date-box">
            <div class="date-large">{{ \Carbon\Carbon::parse($ticket->start_date)->format('d M Y') }}</div>
            <div class="date-small">{{ \Carbon\Carbon::parse($ticket->start_date)->format('H:i') }} uur</div>
        </div>
    </div>

    <!-- Details Grid -->
    <div class="details-grid">
        <!-- Column 1: Attendee Info -->
        <div class="details-col">
            <div class="label">Bezoeker</div>
            <div class="value">{{ $ticket->order->first_name }} {{ $ticket->order->last_name }}</div>

            <div class="label">Locatie</div>
            <div class="value">{{ $ticket->activity->location ?? 'Noordenlicht, Tramstraat 45a, 7848 BL Schoonoord' }}</div>
        </div>

        <!-- Column 2: Order Info -->
        <div class="details-col">
            <div class="label">Bestelnummer</div>
            <div class="value">{{ $ticket->order->order_number }}</div>

            <div class="label">Type</div>
            <div class="value">Toegangsticket</div>
        </div>
    </div>

    <!-- Barcode Section -->
    <div class="barcode-container">
        <!-- Barcode Image -->
        <img src="data:image/png;base64,{{ DNS1D::getBarcodePNG($ticket->uuid, 'C39') }}" alt="barcode" class="barcode-img"/>

        <!-- Human Readable Code below barcode -->
        <div class="ticket-id">{{ $ticket->uuid }}</div>
    </div>

    <!-- Simple Footer -->
    <div class="footer">
        <small>Neem dit ticket mee naar het event (mobiel of geprint).</small>
        <small>Noordenlicht - Natuurlijk Centrum voor Verbinding en BewustZijn</small>
        <small>Cha'kwaini</small>
    </div>

</div>

</body>
</html>
