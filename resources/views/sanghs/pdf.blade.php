<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sangh #{{ $sangh->sangh_sr_no }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size:12px; color:#222; }
        .header { text-align:center; margin-bottom:20px; }
        .section { margin-bottom:12px; }
        table { width:100%; border-collapse: collapse; }
        th, td { padding:8px 6px; border:1px solid #ddd; text-align:left; vertical-align:top; }
        h2 { margin:0 0 8px 0; font-size:16px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Sangh Details — #{{ $sangh->sangh_sr_no }}</h2>
        <div>{{ $sangh->name_of_sangh }}</div>
    </div>

    <div class="section">
        <table>
            <tr><th>Fields</th><th>Details</th></tr>
             <tr><td>Sangh Sr. No</td><td>{{ $sangh->sangh_sr_no }}</td></tr>
            <tr><td>Name of Sangh</td><td>{{ $sangh->name_of_sangh }}</td></tr>
            <tr><td>District</td><td>{{ $sangh->district }}</td></tr>
            <tr><td>District No.</td><td>{{ $sangh->district_no }}</td></tr>
            <tr><td>Taluka</td><td>{{ $sangh->taluka }}</td></tr>
            <tr><td>City</td><td>{{ $sangh->city }}</td></tr>
            <tr><td>Division</td><td>{{ $sangh->division }}</td></tr>
            <tr><td>Division No.</td><td>{{ $sangh->division_no }}</td></tr>
            <tr><td>Total M/F</td><td>{{ $sangh->total_m_f }}</td></tr>

            <tr><td>Meeting Date</td><td>{{ $sangh->date_meeting?->format('Y-m-d') }}</td></tr>
            <tr><td>Receipt No.</td><td>{{ $sangh->receipt_no }}</td></tr>
            <tr><td>Receipt Date</td><td>{{ $sangh->receipt_date?->format('Y-m-d') }}</td></tr>
            <tr><td>Receipt Amount</td><td>{{ $sangh->receipt_amount }}</td></tr>
            <tr><td>Division Membership No.</td><td>{{ $sangh->division_membership_no }}</td></tr>

            <tr><td>Male</td><td>{{ $sangh->male }}</td></tr>
            <tr><td>Female</td><td>{{ $sangh->female }}</td></tr>
            <tr><td>Total Members</td><td>{{ $sangh->total_members }}</td></tr>

            <tr><td>President</td><td>{{ $sangh->president }}</td></tr>
            <tr><td>Secretary</td><td>{{ $sangh->secretary }}</td></tr>
            <tr><td>Tel No.</td><td>{{ $sangh->tel_no }}</td></tr>
            <tr><td>Alt Tel No.</td><td>{{ $sangh->alt_tel_no }}</td></tr>
            <tr><td>Email</td><td>{{ $sangh->email }}</td></tr>

            <tr><td>Address</td><td>{{ $sangh->address }}</td></tr>

            <tr><td>Created By</td><td>{{ $sangh->creator->name ?? $sangh->created_by }}</td></tr>
            <tr><td>Created Date</td><td>{{ $sangh->created_date?->format('Y-m-d H:i:s') ?? $sangh->created_at->format('Y-m-d H:i:s') }}</td></tr>
        </table>
    </div>
</body>
</html>
