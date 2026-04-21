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
        @php($categoryCode = strtoupper((string) old('category_code', $sangh->category_code ?? '')))
        <select name="category_code" class="form-control" required>
            <option value="">Select श्रेणी</option>
            <option value="R" {{ $categoryCode === 'R' ? 'selected' : '' }}>R</option>
            <option value="U" {{ $categoryCode === 'U' ? 'selected' : '' }}>U</option>
            <option value="A" {{ $categoryCode === 'A' ? 'selected' : '' }}>A</option>
        </select>
    </div>
    <div class="form-group col-md-2">
        <label>संघ प्रकार (G/F)</label>
        @php($sanghTypeCode = strtoupper((string) old('sangh_type_code', $sangh->sangh_type_code ?? '')))
        <select name="sangh_type_code" class="form-control" required>
            <option value="">Select प्रकार</option>
            <option value="G" {{ $sanghTypeCode === 'G' ? 'selected' : '' }}>G</option>
            <option value="F" {{ $sanghTypeCode === 'F' ? 'selected' : '' }}>F</option>
        </select>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-4">
        <label>प्रादेशिक विभाग</label>
        @php($pradeshikVibhag = old('pradeshik_vibhag', $sangh->pradeshik_vibhag ?? ''))
        <select name="pradeshik_vibhag" id="pradeshik_vibhag" class="form-control" required>
            <option value="">Select प्रादेशिक विभाग</option>
            <option value="अहिल्यानगर" data-code="AN" {{ $pradeshikVibhag === 'अहिल्यानगर' ? 'selected' : '' }}>अहिल्यानगर</option>
            <option value="बुलढाणा" data-code="BD" {{ $pradeshikVibhag === 'बुलढाणा' ? 'selected' : '' }}>बुलढाणा</option>
            <option value="पूर्व.विदर्भ" data-code="EV" {{ $pradeshikVibhag === 'पूर्व.विदर्भ' ? 'selected' : '' }}>पूर्व.विदर्भ</option>
            <option value="खानदेश" data-code="KD" {{ $pradeshikVibhag === 'खानदेश' ? 'selected' : '' }}>खानदेश</option>
            <option value="कोकण" data-code="KK" {{ $pradeshikVibhag === 'कोकण' ? 'selected' : '' }}>कोकण</option>
            <option value="कोल्हापूर" data-code="KR" {{ $pradeshikVibhag === 'कोल्हापूर' ? 'selected' : '' }}>कोल्हापूर</option>
            <option value="मुंबई" data-code="MM" {{ $pradeshikVibhag === 'मुंबई' ? 'selected' : '' }}>मुंबई</option>
            <option value="नाशिक" data-code="NS" {{ $pradeshikVibhag === 'नाशिक' ? 'selected' : '' }}>नाशिक</option>
            <option value="उत्तर.मराठवाडा" data-code="UM" {{ $pradeshikVibhag === 'उत्तर.मराठवाडा' ? 'selected' : '' }}>उत्तर.मराठवाडा</option>
            <option value="पुणे" data-code="PN" {{ $pradeshikVibhag === 'पुणे' ? 'selected' : '' }}>पुणे</option>
            <option value="सांगली" data-code="SG" {{ $pradeshikVibhag === 'सांगली' ? 'selected' : '' }}>सांगली</option>
            <option value="दक्षिण.मराठवाडा" data-code="SM" {{ $pradeshikVibhag === 'दक्षिण.मराठवाडा' ? 'selected' : '' }}>दक्षिण.मराठवाडा</option>
            <option value="वनवैभव" data-code="VV" {{ $pradeshikVibhag === 'वनवैभव' ? 'selected' : '' }}>वनवैभव</option>
            <option value="पश्चिम.विदर्भ" data-code="WV" {{ $pradeshikVibhag === 'पश्चिम.विदर्भ' ? 'selected' : '' }}>पश्चिम.विदर्भ</option>
        </select>
    </div>
    <div class="form-group col-md-2">
        <label>प्रादेशिक कोड</label>
        <input type="text" id="pradeshik_vibhag_code" name="pradeshik_vibhag_code" class="form-control" value="{{ old('pradeshik_vibhag_code', $sangh->pradeshik_vibhag_code ?? '') }}" readonly>
    </div>
    <div class="form-group col-md-4">
        <label>जिल्हा</label>
        @php($district = old('district', $sangh->district ?? ''))
        <select name="district" id="district" class="form-control" required>
            <option value="">Select जिल्हा</option>
            <option value="अकोला" data-code="AK" {{ $district === 'अकोला' ? 'selected' : '' }}>अकोला</option>
            <option value="अमरावती" data-code="AM" {{ $district === 'अमरावती' ? 'selected' : '' }}>अमरावती</option>
            <option value="अहिल्यानगर" data-code="AN" {{ $district === 'अहिल्यानगर' ? 'selected' : '' }}>अहिल्यानगर</option>
            <option value="बीड" data-code="BD" {{ $district === 'बीड' ? 'selected' : '' }}>बीड</option>
            <option value="भंडारा" data-code="BH" {{ $district === 'भंडारा' ? 'selected' : '' }}>भंडारा</option>
            <option value="बुलढाणा" data-code="BL" {{ $district === 'बुलढाणा' ? 'selected' : '' }}>बुलढाणा</option>
            <option value="चंद्रपूर" data-code="CP" {{ $district === 'चंद्रपूर' ? 'selected' : '' }}>चंद्रपूर</option>
            <option value="धाराशिव" data-code="DS" {{ $district === 'धाराशिव' ? 'selected' : '' }}>धाराशिव</option>
            <option value="धुळे" data-code="DH" {{ $district === 'धुळे' ? 'selected' : '' }}>धुळे</option>
            <option value="गडचिरोली" data-code="GC" {{ $district === 'गडचिरोली' ? 'selected' : '' }}>गडचिरोली</option>
            <option value="गोंदिया" data-code="GY" {{ $district === 'गोंदिया' ? 'selected' : '' }}>गोंदिया</option>
            <option value="हिंगोली" data-code="HG" {{ $district === 'हिंगोली' ? 'selected' : '' }}>हिंगोली</option>
            <option value="जळगाव" data-code="JG" {{ $district === 'जळगाव' ? 'selected' : '' }}>जळगाव</option>
            <option value="जालना" data-code="JL" {{ $district === 'जालना' ? 'selected' : '' }}>जालना</option>
            <option value="कोल्हापूर" data-code="KR" {{ $district === 'कोल्हापूर' ? 'selected' : '' }}>कोल्हापूर</option>
            <option value="लातूर" data-code="LT" {{ $district === 'लातूर' ? 'selected' : '' }}>लातूर</option>
            <option value="मुंबई सिटी" data-code="MC" {{ $district === 'मुंबई सिटी' ? 'selected' : '' }}>मुंबई सिटी</option>
            <option value="मुंबई इतर" data-code="MO" {{ $district === 'मुंबई इतर' ? 'selected' : '' }}>मुंबई इतर</option>
            <option value="नागपूर" data-code="NG" {{ $district === 'नागपूर' ? 'selected' : '' }}>नागपूर</option>
            <option value="नांदेड" data-code="ND" {{ $district === 'नांदेड' ? 'selected' : '' }}>नांदेड</option>
            <option value="नंदुरबार" data-code="NB" {{ $district === 'नंदुरबार' ? 'selected' : '' }}>नंदुरबार</option>
            <option value="नाशिक" data-code="NS" {{ $district === 'नाशिक' ? 'selected' : '' }}>नाशिक</option>
            <option value="पालघर" data-code="PL" {{ $district === 'पालघर' ? 'selected' : '' }}>पालघर</option>
            <option value="परभणी" data-code="PB" {{ $district === 'परभणी' ? 'selected' : '' }}>परभणी</option>
            <option value="पुणे" data-code="PN" {{ $district === 'पुणे' ? 'selected' : '' }}>पुणे</option>
            <option value="रायगड" data-code="RG" {{ $district === 'रायगड' ? 'selected' : '' }}>रायगड</option>
            <option value="रत्नागिरी" data-code="RT" {{ $district === 'रत्नागिरी' ? 'selected' : '' }}>रत्नागिरी</option>
            <option value="सांगली" data-code="SG" {{ $district === 'सांगली' ? 'selected' : '' }}>सांगली</option>
            <option value="सिंधुदुर्ग" data-code="SD" {{ $district === 'सिंधुदुर्ग' ? 'selected' : '' }}>सिंधुदुर्ग</option>
            <option value="सोलापूर" data-code="SL" {{ $district === 'सोलापूर' ? 'selected' : '' }}>सोलापूर</option>
            <option value="संभाजी नगर" data-code="SN" {{ $district === 'संभाजी नगर' ? 'selected' : '' }}>संभाजी नगर</option>
            <option value="सातारा" data-code="ST" {{ $district === 'सातारा' ? 'selected' : '' }}>सातारा</option>
            <option value="ठाणे" data-code="TH" {{ $district === 'ठाणे' ? 'selected' : '' }}>ठाणे</option>
            <option value="वर्धा" data-code="VR" {{ $district === 'वर्धा' ? 'selected' : '' }}>वर्धा</option>
            <option value="वाशीम" data-code="VS" {{ $district === 'वाशीम' ? 'selected' : '' }}>वाशीम</option>
            <option value="यवतमाळ" data-code="YM" {{ $district === 'यवतमाळ' ? 'selected' : '' }}>यवतमाळ</option>
        </select>
    </div>
    <div class="form-group col-md-2">
        <label>जिल्हा कोड</label>
        <input type="text" id="district_code" name="district_code" class="form-control" value="{{ old('district_code', $sangh->district_code ?? '') }}" readonly>
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

<script>
    (function () {
        const mapCode = (selectId, inputId) => {
            const select = document.getElementById(selectId);
            const input = document.getElementById(inputId);

            if (!select || !input) {
                return;
            }

            const sync = () => {
                const option = select.options[select.selectedIndex];
                input.value = option ? (option.getAttribute('data-code') || '') : '';
            };

            select.addEventListener('change', sync);
            sync();
        };

        mapCode('pradeshik_vibhag', 'pradeshik_vibhag_code');
        mapCode('district', 'district_code');
    })();
</script>