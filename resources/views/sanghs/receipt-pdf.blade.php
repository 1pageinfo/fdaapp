<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Receipt - {{ $renewal->renewal_year }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Dejavu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
        }

        .receipt-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 2px solid #333;
            background-color: #fafafa;
        }

        .receipt-header {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #333;
        }

        .receipt-header h1 {
            font-size: 24px;
            font-weight: bold;
            margin: 5px 0;
        }

        .receipt-header .subtitle {
            font-size: 11px;
            color: #666;
            margin: 5px 0;
        }

        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 15px;
        }

        .receipt-details {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .detail-row {
            display: table-row;
        }

        .detail-label {
            display: table-cell;
            width: 50%;
            padding: 8px 10px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
        }

        .detail-value {
            display: table-cell;
            width: 50%;
            padding: 8px 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        .divider {
            margin: 15px 0;
            border-top: 1px dashed #ccc;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        table th {
            background-color: #f0f0f0;
            padding: 10px;
            border-bottom: 2px solid #333;
            text-align: left;
            font-weight: bold;
        }

        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        table tr:last-child td {
            border-bottom: 2px solid #333;
        }

        table tr.total-row {
            background-color: #fff3cd;
        }

        table tr.total-row td {
            font-weight: bold;
            border-bottom: 2px solid #333;
        }

        table tr.paid-row {
            background-color: #e8f5e9;
        }

        table tr.paid-row td {
            font-weight: bold;
            color: #27ae60;
            border-bottom: 2px solid #333;
        }

        .align-right {
            text-align: right;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
        }

        .badge-success {
            background-color: #28a745;
            color: white;
        }

        .badge-warning {
            background-color: #ffc107;
            color: black;
        }

        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
            font-size: 10px;
            color: #999;
        }

        .currency {
            text-align: right;
            font-weight: bold;
        }

        .section-title {
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 10px;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="receipt-container">
        <!-- Receipt Header -->
        <div class="receipt-header">
            <h1>📄 RECEIPT</h1>
            <div class="subtitle">Sangh Renewal Receipt</div>
            <div class="subtitle">FESKCM Organization</div>
        </div>

        <!-- Sangh Details -->
        <div class="receipt-details">
            <div class="detail-row">
                <div class="detail-label">संघ नाव (Sangh Name):</div>
                <div class="detail-value">{{ $sangh->name_of_sangh }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">संघाचा अनु क्र. (Unique Ref No):</div>
                <div class="detail-value">{{ $sangh->unique_ref_no }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">जिल्हा (District):</div>
                <div class="detail-value">{{ $sangh->district }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">वर्ष (Year):</div>
                <div class="detail-value">{{ $renewal->renewal_year }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">दिनांक (Date):</div>
                <div class="detail-value">{{ now()->format('d-m-Y') }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">स्थिति (Status):</div>
                <div class="detail-value">
                    <span class="badge badge-success">PAID</span>
                </div>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Receipt Items -->
        <div class="section-title">रसीद तपशील (Receipt Details)</div>
        <table>
            <thead>
                <tr>
                    <th>विवरण (Description)</th>
                    <th class="currency">रक्कम (Amount)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>वार्षिक शुल्क (Annual Fee)</td>
                    <td class="currency">₹ {{ number_format($renewal->annual_fee ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>विकास निधी शुल्क (Development Fund Fee)</td>
                    <td class="currency">₹ {{ number_format($renewal->development_fee ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>दंड शुल्क (Penalty Fee)</td>
                    <td class="currency">₹ {{ number_format($renewal->penalty_fee ?? 0, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td>एकूण रक्कम (Total Amount)</td>
                    <td class="currency">₹ {{ number_format(($renewal->annual_fee ?? 0) + ($renewal->development_fee ?? 0) + ($renewal->penalty_fee ?? 0), 2) }}</td>
                </tr>
                <tr class="paid-row">
                    <td>भरलेली रक्कम (Paid Amount)</td>
                    <td class="currency">₹ {{ number_format($renewal->paid_amount ?? 0, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- Additional Information -->
        <div class="section-title">अतिरिक्त माहिती (Additional Information)</div>
        <div class="receipt-details">
            <div class="detail-row">
                <div class="detail-label">फेस्कॉम पावती क्र. (Receipt No):</div>
                <div class="detail-value">{{ $renewal->feskcom_receipt_no ?? 'N/A' }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">पावती दिनांक (Receipt Date):</div>
                <div class="detail-value">{{ optional($renewal->feskcom_receipt_date)->format('d-m-Y') ?? 'N/A' }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">पुरुष सदस्य (Male Members):</div>
                <div class="detail-value">{{ $renewal->male_members ?? 0 }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">महिला सदस्य (Female Members):</div>
                <div class="detail-value">{{ $renewal->female_members ?? 0 }}</div>
            </div>
            <div class="detail-row">
                <div class="detail-label">एकूण सदस्य (Total Members):</div>
                <div class="detail-value">{{ $renewal->total_members ?? 0 }}</div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>This is an automated receipt. Please retain for your records.</p>
            <p style="margin-top: 5px;">Generated on {{ now()->format('d-m-Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>
