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
            <tr><td>Unique संघाचा अनु क्र.</td><td>{{ $sangh->unique_ref_no }}</td></tr>
            <tr><td>प्रादेशिक विभागातील संघाचा अनु क्र.</td><td>{{ $sangh->pradeshik_ref_no }}</td></tr>
            <tr><td>जिल्हा मधे संघाचा अनु. क्र.</td><td>{{ $sangh->district_ref_no }}</td></tr>
            <tr><td>वर्ष</td><td>{{ $sangh->registration_year }}</td></tr>
            <tr><td>संघाचे नाव</td><td>{{ $sangh->name_of_sangh }}</td></tr>
            <tr><td>श्रेणी</td><td>{{ $sangh->category_code }}</td></tr>
            <tr><td>संघ प्रकार</td><td>{{ $sangh->sangh_type_code }}</td></tr>
            <tr><td>प्रादेशिक विभाग</td><td>{{ $sangh->pradeshik_vibhag }}</td></tr>
            <tr><td>जिल्हा</td><td>{{ $sangh->district }}</td></tr>
            <tr><td>तालुका</td><td>{{ $sangh->taluka }}</td></tr>
            <tr><td>गाव</td><td>{{ $sangh->village }}</td></tr>
            <tr><td>शहर</td><td>{{ $sangh->city }}</td></tr>
            <tr><td>मुक्काम पोस्ट</td><td>{{ $sangh->mukkam_post }}</td></tr>
            <tr><td>पिनकोड</td><td>{{ $sangh->pincode }}</td></tr>
            <tr><td>पत्ता</td><td>{{ $sangh->address }}</td></tr>
            <tr><td>रस्ता / पथ</td><td>{{ $sangh->road_path }}</td></tr>
            <tr><td>विभाग/प्रभाग</td><td>{{ $sangh->ward_section }}</td></tr>
            <tr><td>पुरुष सभासद संख्या</td><td>{{ $sangh->male }}</td></tr>
            <tr><td>महिला सभासद संख्या</td><td>{{ $sangh->female }}</td></tr>
            <tr><td>एकूण सभासद संख्या</td><td>{{ $sangh->total_members }}</td></tr>
            <tr><td>अध्यक्ष</td><td>{{ $sangh->president }}</td></tr>
            <tr><td>अध्यक्ष मोबाईल</td><td>{{ $sangh->president_phone }}</td></tr>
            <tr><td>अध्यक्ष व्हॉट्सअप</td><td>{{ $sangh->president_whatsapp }}</td></tr>
            <tr><td>अध्यक्ष इमेल</td><td>{{ $sangh->president_email }}</td></tr>
            <tr><td>सचिव</td><td>{{ $sangh->secretary }}</td></tr>
            <tr><td>सचिव मोबाईल</td><td>{{ $sangh->secretary_phone }}</td></tr>
            <tr><td>सचिव व्हॉट्सअप</td><td>{{ $sangh->secretary_whatsapp }}</td></tr>
            <tr><td>सचिव इमेल</td><td>{{ $sangh->secretary_email }}</td></tr>

            <tr><td>Created By</td><td>{{ $sangh->creator->name ?? $sangh->created_by }}</td></tr>
            <tr><td>Created Date</td><td>{{ $sangh->created_date?->format('Y-m-d H:i:s') ?? $sangh->created_at->format('Y-m-d H:i:s') }}</td></tr>
        </table>
    </div>
</body>
</html>
