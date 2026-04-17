@extends('layouts.app')

@section('content')
    <div class="container mt-4">

        <div class="d-flex justify-content-between mb-3">
            <h2>Sangh Details — #{{ $sangh->sangh_sr_no }}</h2>
            <div>
                <a href="{{ route('sanghs.edit', $sangh) }}" class="btn btn-sm btn-primary">Edit</a>
                <a href="{{ route('sanghs.index') }}" class="btn btn-sm btn-secondary">Back</a>
                <a href="{{ route('sanghs.pdf', $sangh) }}" class="btn btn-sm btn-primary">Download PDF</a>
            </div>
        </div>
        <hr>

        
        <div class="card p-4 shadow-sm">

            <h4 class="mb-3">Basic Information</h4>
            <table class="table table-bordered">
                <tr>
                    <th>Sangh Sr. No</th>
                    <td>{{ $sangh->sangh_sr_no }}</td>
                </tr>
                <tr>
                    <th>Name of Sangh</th>
                    <td>{{ $sangh->name_of_sangh }}</td>
                </tr>
                <tr>
                    <th>District</th>
                    <td>{{ $sangh->district }}</td>
                </tr>
                <tr>
                    <th>District No.</th>
                    <td>{{ $sangh->district_no }}</td>
                </tr>
                <tr>
                    <th>Taluka</th>
                    <td>{{ $sangh->taluka }}</td>
                </tr>
                <tr>
                    <th>City</th>
                    <td>{{ $sangh->city }}</td>
                </tr>
                <tr>
                    <th>Division</th>
                    <td>{{ $sangh->division }}</td>
                </tr>
                <tr>
                    <th>Division No.</th>
                    <td>{{ $sangh->division_no }}</td>
                </tr>
                <tr>
                    <th>Total M/F</th>
                    <td>{{ $sangh->total_m_f }}</td>
                </tr>
            </table>

            <h4 class="mt-4 mb-3">Receipt & Meeting Details</h4>
            <table class="table table-bordered">
                <tr>
                    <th>Meeting Date</th>
                    <td>{{ $sangh->date_meeting?->format('Y-m-d') }}</td>
                </tr>
                <tr>
                    <th>Receipt No.</th>
                    <td>{{ $sangh->receipt_no }}</td>
                </tr>
                <tr>
                    <th>Receipt Date</th>
                    <td>{{ $sangh->receipt_date?->format('Y-m-d') }}</td>
                </tr>
                <tr>
                    <th>Receipt Amount</th>
                    <td>{{ $sangh->receipt_amount }}</td>
                </tr>
                <tr>
                    <th>Division Membership No.</th>
                    <td>{{ $sangh->division_membership_no }}</td>
                </tr>
            </table>

            <h4 class="mt-4 mb-3">Membership Count</h4>
            <table class="table table-bordered">
                <tr>
                    <th>Male</th>
                    <td>{{ $sangh->male }}</td>
                </tr>
                <tr>
                    <th>Female</th>
                    <td>{{ $sangh->female }}</td>
                </tr>
                <tr>
                    <th>Total Members</th>
                    <td>{{ $sangh->total_members }}</td>
                </tr>
            </table>

            <h4 class="mt-4 mb-3">Contact Information</h4>
            <table class="table table-bordered">
                <tr>
                    <th>President</th>
                    <td>{{ $sangh->president }}</td>
                </tr>
                <tr>
                    <th>Secretary</th>
                    <td>{{ $sangh->secretary }}</td>
                </tr>
                <tr>
                    <th>Tel. No.</th>
                    <td>{{ $sangh->tel_no }}</td>
                </tr>
                <tr>
                    <th>Alt Tel. No.</th>
                    <td>{{ $sangh->alt_tel_no }}</td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td>{{ $sangh->email }}</td>
                </tr>
            </table>

            <h4 class="mt-4 mb-3">Address</h4>
            <div class="p-3 border rounded">
                {{ $sangh->address }}
            </div>

            <h4 class="mt-4 mb-3">System Information</h4>
            <table class="table table-bordered">
                <tr>
                    <th>Created By</th>
                    <td>{{ $sangh->creator->name ?? 'N/A' }}</td>
                </tr>


                <tr>
                    <th>Created Date</th>
                    <td>{{ $sangh->created_date?->format('Y-m-d H:i:s') }}</td>
                </tr>
            </table>

        </div>
    </div>
@endsection