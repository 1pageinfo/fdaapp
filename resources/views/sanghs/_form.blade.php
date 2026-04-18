<div class="form-row">
    <div class="form-group col-md-3">
        <label>वर्ष</label>
        <input type="number" min="1900" max="{{ date('Y') }}" name="registration_year" class="form-control" value="{{ old('registration_year', $sangh->registration_year ?? date('Y')) }}">
    </div>
    <div class="form-group col-md-5">
        <label>संघाचे नाव</label>
        <input type="text" name="name_of_sangh" class="form-control" value="{{ old('name_of_sangh', $sangh->name_of_sangh ?? '') }}" required>
    </div>
    <div class="form-group col-md-2">
        <label>श्रेणी (R/U/A)</label>
        <input type="text" maxlength="1" name="category_code" class="form-control" value="{{ old('category_code', $sangh->category_code ?? '') }}">
    </div>
    <div class="form-group col-md-2">
        <label>संघ प्रकार (G/F)</label>
        <input type="text" maxlength="1" name="sangh_type_code" class="form-control" value="{{ old('sangh_type_code', $sangh->sangh_type_code ?? '') }}">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label>प्रादेशिक विभाग</label>
        <input type="text" name="pradeshik_vibhag" class="form-control" value="{{ old('pradeshik_vibhag', $sangh->pradeshik_vibhag ?? '') }}">
    </div>
    <div class="form-group col-md-2">
        <label>प्रादेशिक कोड</label>
        <input type="text" name="pradeshik_vibhag_code" class="form-control" value="{{ old('pradeshik_vibhag_code', $sangh->pradeshik_vibhag_code ?? '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>जिल्हा</label>
        <input type="text" name="district" class="form-control" value="{{ old('district', $sangh->district ?? '') }}">
    </div>
    <div class="form-group col-md-2">
        <label>जिल्हा कोड</label>
        <input type="text" name="district_code" class="form-control" value="{{ old('district_code', $sangh->district_code ?? '') }}">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-3">
        <label>तालुका</label>
        <input type="text" name="taluka" class="form-control" value="{{ old('taluka', $sangh->taluka ?? '') }}">
    </div>
    <div class="form-group col-md-3">
        <label>गाव</label>
        <input type="text" name="village" class="form-control" value="{{ old('village', $sangh->village ?? '') }}">
    </div>
    <div class="form-group col-md-3">
        <label>शहर</label>
        <input type="text" name="city" class="form-control" value="{{ old('city', $sangh->city ?? '') }}">
    </div>
    <div class="form-group col-md-3">
        <label>मुक्काम पोस्ट</label>
        <input type="text" name="mukkam_post" class="form-control" value="{{ old('mukkam_post', $sangh->mukkam_post ?? '') }}">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-2">
        <label>पिनकोड</label>
        <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $sangh->pincode ?? '') }}">
    </div>
    <div class="form-group col-md-5">
        <label>पत्ता</label>
        <input type="text" name="address" class="form-control" value="{{ old('address', $sangh->address ?? '') }}">
    </div>
    <div class="form-group col-md-3">
        <label>रस्ता / पथ</label>
        <input type="text" name="road_path" class="form-control" value="{{ old('road_path', $sangh->road_path ?? '') }}">
    </div>
    <div class="form-group col-md-2">
        <label>विभाग/प्रभाग</label>
        <input type="text" name="ward_section" class="form-control" value="{{ old('ward_section', $sangh->ward_section ?? '') }}">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-2">
        <label>पुरुष सभासद</label>
        <input type="number" min="0" name="male" class="form-control" value="{{ old('male', $sangh->male ?? '') }}">
    </div>
    <div class="form-group col-md-2">
        <label>महिला सभासद</label>
        <input type="number" min="0" name="female" class="form-control" value="{{ old('female', $sangh->female ?? '') }}">
    </div>
    <div class="form-group col-md-2">
        <label>एकूण सभासद</label>
        <input type="number" min="0" name="total_members" class="form-control" value="{{ old('total_members', $sangh->total_members ?? '') }}">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-3">
        <label>अध्यक्ष</label>
        <input type="text" name="president" class="form-control" value="{{ old('president', $sangh->president ?? '') }}">
    </div>
    <div class="form-group col-md-3">
        <label>मो.फ़ोन नंबर</label>
        <input type="text" name="president_phone" class="form-control" value="{{ old('president_phone', $sangh->president_phone ?? '') }}">
    </div>
    <div class="form-group col-md-3">
        <label>व्हॉट्सअप नंबर</label>
        <input type="text" name="president_whatsapp" class="form-control" value="{{ old('president_whatsapp', $sangh->president_whatsapp ?? '') }}">
    </div>
    <div class="form-group col-md-3">
        <label>इमेल</label>
        <input type="email" name="president_email" class="form-control" value="{{ old('president_email', $sangh->president_email ?? '') }}">
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-3">
        <label>सचिव</label>
        <input type="text" name="secretary" class="form-control" value="{{ old('secretary', $sangh->secretary ?? '') }}">
    </div>
    <div class="form-group col-md-3">
        <label>मो.फ़ोन नंबर</label>
        <input type="text" name="secretary_phone" class="form-control" value="{{ old('secretary_phone', $sangh->secretary_phone ?? '') }}">
    </div>
    <div class="form-group col-md-3">
        <label>व्हॉट्सअप नंबर</label>
        <input type="text" name="secretary_whatsapp" class="form-control" value="{{ old('secretary_whatsapp', $sangh->secretary_whatsapp ?? '') }}">
    </div>
    <div class="form-group col-md-3">
        <label>इमेल</label>
        <input type="email" name="secretary_email" class="form-control" value="{{ old('secretary_email', $sangh->secretary_email ?? '') }}">
    </div>
</div>