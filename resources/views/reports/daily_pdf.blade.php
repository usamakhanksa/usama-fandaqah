<!DOCTYPE html>
<html>
<head>
    <title>Daily Report - {{ $date }}</title>
    <style>
        body { font-family: sans-serif; color: #333; }
        .header { text-align: center; margin-bottom: 30px; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #eee; padding: 8px; text-align: left; }
        th { background-color: #f9f9f9; }
        .stat-grid { display: flex; flex-wrap: wrap; }
        .stat-item { width: 25%; margin-bottom: 10px; }
        .label { color: #666; font-size: 12px; }
        .value { font-weight: bold; font-size: 16px; }
        .total-revenue { color: #10b981; font-size: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Report</h1>
        <p>{{ $date }}</p>
    </div>

    <div class="section">
        <div class="section-title">Room Statistics</div>
        <table>
            <tr>
                <th>Total Rooms</th>
                <td>{{ $occupancy['total_rooms'] }}</td>
                <th>Occupied</th>
                <td>{{ $occupancy['occupied'] }}</td>
            </tr>
            <tr>
                <th>Available</th>
                <td>{{ $occupancy['available'] }}</td>
                <th>Occupancy %</th>
                <td>{{ $occupancy['occupancy_percentage'] }}%</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Revenue Summary</div>
        <table>
            <tr>
                <th>Room Revenue</th>
                <td>{{ number_format($revenue['room_revenue'], 2) }} SR</td>
            </tr>
            <tr>
                <th>F&B Revenue</th>
                <td>{{ number_format($revenue['fb_revenue'], 2) }} SR</td>
            </tr>
            <tr>
                <th>Total Revenue</th>
                <td class="total-revenue">{{ number_format($revenue['total_revenue'], 2) }} SR</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Arrivals</div>
        <table>
            <thead>
                <tr>
                    <th>Guest</th>
                    <th>Room</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($arrivals as $res)
                <tr>
                    <td>{{ $res['guest']['first_name'] ?? '' }} {{ $res['guest']['last_name'] ?? '' }}</td>
                    <td>{{ $res['unit']['unit_number'] ?? 'N/A' }}</td>
                    <td>{{ strtoupper($res['status']) }}</td>
                </tr>
                @endforeach
                @if(count($arrivals) == 0)
                <tr><td colspan="3">No arrivals</td></tr>
                @endif
            </tbody>
        </table>
    </div>
</body>
</html>
