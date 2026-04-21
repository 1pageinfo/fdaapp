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

<input
    type="hidden"
    name="address_type"
    id="address_type"
    value="{{ old('address_type', (!empty(optional($sangh)->address) || (!empty(optional($sangh)->city) && empty(optional($sangh)->village))) ? 'city' : 'village') }}"
>

{{-- Address Type Toggle --}}
<div class="form-row mb-2">
    <div class="col-12">
        <label class="font-weight-bold mr-2">पत्ता प्रकार:</label>
        <div class="btn-group" role="group">
            <button type="button" id="btn_addr_village"
                class="btn btn-sm {{ old('address_type', (!empty(optional($sangh)->address) || (!empty(optional($sangh)->city) && empty(optional($sangh)->village))) ? 'city' : 'village') === 'village' ? 'btn-primary' : 'btn-outline-primary' }}">
                गाव / Village
            </button>
            <button type="button" id="btn_addr_city"
                class="btn btn-sm {{ old('address_type', (!empty(optional($sangh)->address) || (!empty(optional($sangh)->city) && empty(optional($sangh)->village))) ? 'city' : 'village') === 'city' ? 'btn-primary' : 'btn-outline-primary' }}">
                शहर / City
            </button>
        </div>
    </div>
</div>

{{-- Village Address --}}
<div id="section_village_addr" class="form-row" @if(old('address_type', (!empty(optional($sangh)->address) || (!empty(optional($sangh)->city) && empty(optional($sangh)->village))) ? 'city' : 'village') === 'city') style="display:none" @endif>
    <div class="form-group col-md-4">
        <label>तालुका</label>
        <select name="taluka" id="taluka" class="form-control"><option value="">Select तालुका</option></select>
    </div>
    <div class="form-group col-md-4">
        <label>गाव</label>
        <input type="text" name="village" class="form-control" value="{{ old('village', $sangh->village ?? '') }}">
    </div>
    <div class="form-group col-md-4">
        <label>मुक्काम पोस्ट</label>
        <input type="text" name="mukkam_post" class="form-control" value="{{ old('mukkam_post', $sangh->mukkam_post ?? '') }}">
    </div>
</div>

{{-- City Address --}}
<div id="section_city_addr" class="form-row" @if(old('address_type', (!empty(optional($sangh)->address) || (!empty(optional($sangh)->city) && empty(optional($sangh)->village))) ? 'city' : 'village') === 'village') style="display:none" @endif>
    <div class="form-group col-md-4">
        <label>पत्ता / Address</label>
        <input type="text" name="address" class="form-control" value="{{ old('address', $sangh->address ?? '') }}">
    </div>
    <div class="form-group col-md-3">
        <label>रस्ता / पथ / Road</label>
        <input type="text" name="road_path" class="form-control" value="{{ old('road_path', $sangh->road_path ?? '') }}">
    </div>
    <div class="form-group col-md-2">
        <label>विभाग / प्रभाग / Area</label>
        <input type="text" name="ward_section" class="form-control" value="{{ old('ward_section', $sangh->ward_section ?? '') }}">
    </div>
    <div class="form-group col-md-3">
        <label>शहर / City</label>
        @php($city = old('city', $sangh->city ?? ''))
        @php($cityOptions = [
            'मुंबई',
            'पुणे',
            'नागपूर',
            'ठाणे',
            'पिंपरी-चिंचवड',
            'नाशिक',
            'कल्याण-डोंबिवली',
            'वसई-विरार',
            'औरंगाबाद',
            'नवी मुंबई',
            'सोलापूर',
            'मीरा-भायंदर',
            'भिवंडी-निजामपूर',
            'अमरावती',
            'नांदेड-वाघाला',
            'कोल्हापूर',
            'उल्हासनगर',
            'सांगली',
            'मालेगाव',
            'जळगाव',
            'अकोला',
            'लातूर',
            'धुळे',
            'अहमदनगर',
            'चंद्रपूर',
            'परभणी',
            'इचलकरंजी',
            'जलना',
            'अंबरनाथ',
            'भुसावळ',
            'पनवेल',
            'बदलापूर',
            'बीड',
            'गोंदिया',
            'सतारा',
            'बारशी',
            'यवतमाळ',
            'अचलपूर',
            'उस्मानाबाद',
            'नंदुरबार',
            'वर्धा',
            'उदगीर',
            'हिंगणघाट',
        ])
        <select name="city" class="form-control">
            <option value="">Select शहर</option>
            @if($city !== '' && !in_array($city, $cityOptions, true))
                <option value="{{ $city }}" selected>{{ $city }}</option>
            @endif
            @foreach($cityOptions as $option)
                <option value="{{ $option }}" {{ $city === $option ? 'selected' : '' }}>{{ $option }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- Pincode + State + Country (common) --}}
<div class="form-row align-items-end">
    <div class="form-group col-md-2">
        <label>पिनकोड</label>
        @php($savedPincode = (string) old('pincode', $sangh->pincode ?? ''))
        <input type="hidden" name="pincode" id="pincode" value="{{ $savedPincode }}">
        <div style="position:relative;">
            <input
                type="text"
                id="pincode_search"
                class="form-control"
                value="{{ $savedPincode }}"
                placeholder="Search पिनकोड"
                inputmode="numeric"
                autocomplete="off"
            >
            <ul id="pincode_results" style="display:none;position:absolute;top:100%;left:0;right:0;z-index:1050;background:#fff;border:1px solid #ced4da;border-top:none;border-radius:0 0 4px 4px;max-height:200px;overflow-y:auto;padding:0;margin:0;list-style:none;"></ul>
        </div>
    </div>
    <div class="form-group col-md-3">
        <label>राज्य / State</label>
        <input type="text" class="form-control" value="Maharashtra" disabled>
    </div>
    <div class="form-group col-md-2">
        <label>देश / Country</label>
        <input type="text" class="form-control" value="India" disabled>
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-2">
        <label>पुरुष सभासद</label>
        <input type="number" min="0" step="1" inputmode="numeric" id="male" name="male" class="form-control" value="{{ old('male', $sangh->male ?? '') }}">
    </div>
    <div class="form-group col-md-2">
        <label>महिला सभासद</label>
        <input type="number" min="0" step="1" inputmode="numeric" id="female" name="female" class="form-control" value="{{ old('female', $sangh->female ?? '') }}">
    </div>
    <div class="form-group col-md-2">
        <label>एकूण सभासद</label>
        <input type="number" min="0" step="1" inputmode="numeric" id="total_members" name="total_members" class="form-control" value="{{ old('total_members', $sangh->total_members ?? '') }}" readonly>
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
        // District → Taluka mapping
        const districtTalukaMap = {
            '\u0905\u0939\u093f\u0932\u094d\u092f\u093e\u0928\u0917\u0930':  ['\u0928\u0917\u0930','\u0936\u0947\u0935\u0917\u093e\u0935','\u092a\u093e\u0925\u0930\u094d\u0921\u0940','\u092a\u093e\u0930\u0928\u0930','\u0938\u0902\u0917\u092e\u0928\u0947\u0930','\u0915\u094b\u092a\u0930\u0917\u093e\u0935','\u0905\u0915\u094b\u0932\u0947','\u0936\u094d\u0930\u0940\u0930\u093e\u092e\u092a\u0942\u0930','\u0928\u0947\u0935\u093e\u0938\u093e','\u0930\u093e\u0939\u0924\u093e','\u0930\u093e\u0939\u0941\u0930\u0940','\u0936\u094d\u0930\u0940\u0917\u094b\u0902\u0926\u093e','\u0915\u0930\u094d\u091c\u0924','\u091c\u093e\u092e\u0916\u0947\u0921'],
            '\u092c\u0941\u0932\u0922\u093e\u0923\u093e':     ['\u092c\u0941\u0932\u0922\u093e\u0923\u093e','\u091a\u093f\u0916\u0932\u0940','\u0926\u0947\u090a\u0933\u0917\u093e\u0935 \u0930\u093e\u091c\u093e','\u091c\u0933\u0917\u093e\u0935 \u091c\u093e\u092e\u094b\u0926','\u0938\u0902\u0917\u094d\u0930\u093e\u092e\u092a\u0942\u0930','\u092e\u0932\u0915\u093e\u092a\u0942\u0930','\u092e\u094b\u091f\u0932\u093e','\u0928\u093e\u0902\u0926\u0941\u0930\u093e','\u0916\u093e\u092e\u0917\u093e\u0935','\u0936\u0947\u0917\u093e\u0935','\u092e\u0947\u0939\u0915\u0930','\u0938\u093f\u0902\u0926\u0916\u0947\u0921 \u0930\u093e\u091c\u093e','\u0932\u094b\u0923\u093e\u0930'],
            '\u092d\u0902\u0921\u093e\u0930\u093e':      ['\u092d\u0902\u0921\u093e\u0930\u093e','\u0924\u0941\u092e\u0938\u0930','\u092a\u094c\u0928\u0940','\u092e\u094b\u0939\u093e\u0921\u0940','\u0938\u093e\u0915\u094b\u0932\u0940','\u0932\u093e\u0916\u0923\u0940','\u0932\u0916\u0902\u0926\u0942\u0930'],
            '\u0928\u093e\u0917\u092a\u0942\u0930':      ['\u0928\u093e\u0917\u092a\u0942\u0930 \u0905\u0930\u094d\u092c\u0928','\u0928\u093e\u0917\u092a\u0942\u0930 \u0930\u0941\u0930\u0932','\u0915\u0902\u092a\u094d\u0924\u0940','\u0939\u093f\u0902\u0917\u0923\u093e','\u0915\u093e\u091f\u094b\u0933','\u0928\u0930\u0916\u0947\u0921','\u0938\u093e\u0935\u0928\u0947\u0930','\u0915\u0932\u092e\u0947\u0936\u094d\u0935\u0930','\u0930\u093e\u092e\u091f\u0947\u0915','\u092e\u094c\u0926\u093e','\u092a\u093e\u0930\u0938\u0947\u0913\u0928\u0940','\u0909\u092e\u0930\u0947\u0921','\u0915\u0941\u0939\u0940','\u092d\u093f\u0935\u093e\u092a\u0942\u0930'],
            '\u0935\u0930\u094d\u0927\u093e':       ['\u0935\u0930\u094d\u0927\u093e','\u0926\u0947\u0913\u0932\u0940','\u0938\u0947\u0932\u0942','\u0905\u0930\u0935\u0940','\u0905\u0937\u094d\u091f\u0940','\u0915\u0930\u0902\u091c\u093e','\u0939\u093f\u0902\u0917\u0923\u0918\u093e\u091f','\u0938\u092e\u0941\u0926\u094d\u0930\u092a\u0942\u0930'],
            '\u0927\u0941\u0933\u0947':        ['\u0927\u0941\u0933\u0947','\u0938\u093e\u0915\u094d\u0930\u0940','\u0938\u093f\u0902\u0926\u0916\u0947\u0921\u093e','\u0936\u093f\u0930\u092a\u0942\u0930'],
            '\u091c\u0933\u0917\u093e\u0935':       ['\u091c\u0933\u0917\u093e\u0935','\u091c\u093e\u092e\u0928\u0947\u0930','\u090f\u0930\u0902\u0921\u094b\u0932','\u0927\u0930\u0923\u0917\u093e\u0935','\u092d\u0941\u0938\u093e\u0935\u0933','\u0930\u093e\u0935\u0947\u0930','\u092e\u0941\u0915\u094d\u0924\u093e\u0908\u0928\u0917\u0930','\u092c\u094b\u0921\u0935\u093e\u0921','\u092f\u093e\u0935\u0932','\u0905\u092e\u0932\u0928\u0947\u0930','\u092a\u093e\u0930\u094b\u0933\u093e','\u091a\u094b\u092a\u0921\u093e','\u092a\u093e\u091a\u094b\u0930\u093e','\u092d\u0921\u0917\u093e\u0935','\u091a\u093e\u0933\u0940\u0938\u0917\u093e\u0935'],
            '\u0928\u0902\u0926\u0941\u0930\u092c\u093e\u0930':    ['\u0928\u0902\u0926\u0941\u0930\u092c\u093e\u0930','\u0928\u0935\u093e\u092a\u0942\u0930','\u0936\u0939\u093e\u0926\u093e','\u0924\u0933\u094b\u0926\u0947','\u0905\u0915\u094d\u0915\u0932\u0915\u0941\u0935\u093e','\u0927\u0921\u0917\u093e\u0935'],
            '\u0920\u093e\u0923\u0947':        ['\u0920\u093e\u0923\u0947','\u0915\u0932\u094d\u092f\u093e\u0923','\u092e\u0941\u0930\u092c\u093e\u0921','\u092d\u093f\u0935\u0902\u0921\u0940','\u0936\u0939\u093e\u092a\u0942\u0930','\u0909\u0932\u094d\u0939\u093e\u0938\u0928\u0917\u0930','\u0905\u0902\u092c\u0930\u0928\u093e\u0925'],
            '\u092a\u093e\u0932\u0918\u0930':       ['\u092a\u093e\u0932\u0918\u0930','\u0935\u0938\u0908','\u0921\u0939\u093e\u0923\u0942','\u0924\u0932\u093e\u0938\u0930\u0940','\u091c\u0935\u094d\u0939\u093e\u0930','\u092e\u094b\u0916\u093e\u0921\u093e','\u0935\u093e\u0921\u093e','\u0935\u093f\u0915\u094d\u0930\u092e\u0917\u0921'],
            '\u0930\u093e\u092f\u0917\u0921':       ['\u092a\u0947\u0928','\u0905\u0932\u093f\u092c\u093e\u0917','\u092e\u0941\u0930\u0941\u0921','\u092a\u0928\u0935\u0947\u0932','\u0909\u0930\u0923','\u0915\u0930\u094d\u091c\u0924','\u0916\u093e\u0932\u093e\u092a\u0942\u0930','\u092e\u093e\u0923\u0917\u093e\u0935','\u0924\u0933\u093e','\u0930\u094b\u0939\u093e','\u0938\u0941\u0927\u093e\u0917\u0921-\u092a\u093e\u0932\u0940','\u092e\u0939\u093e\u0921','\u092a\u094b\u0932\u093e\u0926\u092a\u0942\u0930','\u0936\u094d\u0930\u0940\u0935\u0930\u094d\u0927\u0928','\u092e\u094d\u0939\u0938\u093e\u0933\u093e'],
            '\u0930\u0924\u094d\u0928\u093e\u0917\u093f\u0930\u0940':   ['\u0930\u0924\u094d\u0928\u093e\u0917\u093f\u0930\u0940','\u0938\u0902\u0917\u092e\u0947\u0936\u094d\u0935\u0930','\u0932\u093e\u0902\u091c\u093e','\u0930\u093e\u091c\u093e\u092a\u0942\u0930','\u091a\u093f\u092a\u0933\u0942\u0928','\u0917\u0941\u0939\u093e\u0917\u0930','\u0926\u093e\u092a\u094b\u0932\u0940','\u092e\u0902\u0921\u0923\u0917\u0921','\u0916\u0947\u0921'],
            '\u0938\u093f\u0902\u0927\u0941\u0926\u0941\u0930\u094d\u0917':  ['\u0915\u0923\u0915\u0935\u0932\u0940','\u0935\u0948\u092d\u0935\u0935\u093e\u0921\u0940','\u0926\u0947\u0935\u0917\u0921','\u092e\u093e\u0932\u0935\u0923','\u0938\u093e\u0935\u0902\u0924\u0935\u093e\u0921\u0940','\u0915\u0941\u0921\u093e\u0933','\u0935\u0947\u0902\u0917\u0941\u0930\u094d\u0932\u093e','\u0926\u094b\u0921\u093e\u092e\u093e\u0930\u094d\u0917'],
            '\u0915\u094b\u0932\u094d\u0939\u093e\u092a\u0942\u0930':   ['\u0915\u0930\u0935\u0940\u0930','\u092a\u0928\u094d\u0939\u093e\u0933\u093e','\u0936\u093e\u0939\u0942\u0935\u093e\u0921\u0940','\u0915\u093e\u0917\u0932','\u0939\u093e\u0924\u0915\u0923\u0902\u0917\u0932\u0947','\u0936\u093f\u0930\u094b\u0933','\u0930\u093e\u0927\u093e\u0928\u0917\u0930\u0940','\u0917\u0917\u0928\u092c\u093e\u0935\u0921\u093e','\u092d\u0941\u0926\u0930\u0917\u0921','\u0917\u0922\u093f\u0902\u0917\u0932\u093e\u091c','\u091a\u0902\u0926\u0917\u0921','\u0906\u091c\u0930\u093e'],
            '\u092e\u0941\u0902\u092c\u0908 \u0938\u093f\u091f\u0940':  ['\u092e\u0941\u0902\u092c\u0908 \u0936\u0939\u0930'],
            '\u092e\u0941\u0902\u092c\u0908 \u0907\u0924\u0930':   ['\u0915\u0941\u0930\u094d\u0932\u093e','\u0905\u0902\u0927\u0947\u0930\u0940','\u092c\u094b\u0930\u093f\u0935\u0932\u0940','\u0928\u0935\u0940 \u092e\u0941\u0902\u092c\u0908'],
            '\u0928\u093e\u0936\u093f\u0915':       ['\u0928\u093e\u0936\u093f\u0915','\u0907\u0917\u0924\u092a\u0941\u0930\u0940','\u0926\u093f\u0902\u0921\u094b\u0930\u0940','\u092a\u0947\u0920','\u0924\u094d\u0930\u093f\u0902\u092c\u0915\u0947\u0936\u094d\u0935\u0930','\u0915\u0932\u0935\u093e\u0928','\u0926\u0947\u0913\u0932\u093e','\u0938\u0941\u0930\u0917\u0923\u093e','\u092c\u0917\u0932\u093e\u0928','\u092e\u093e\u0932\u0947\u0917\u093e\u0935','\u0928\u093e\u0902\u0926\u0917\u093e\u0935','\u091a\u093e\u0902\u0926\u0935\u0921','\u0928\u093f\u092b\u093e\u0921','\u0938\u093f\u0928\u094d\u0928\u0930','\u092f\u0947\u0935\u0932\u093e'],
            '\u0938\u0902\u092d\u093e\u091c\u0940 \u0928\u0917\u0930': ['\u0938\u0902\u092d\u093e\u091c\u0940 \u0928\u0917\u0930','\u0915\u0928\u094d\u0928\u0921','\u0938\u094b\u090f\u0917\u093e\u0935','\u0938\u093f\u0932\u094d\u0932\u094b\u0921','\u092b\u0941\u0932\u0902\u092c\u094d\u0930\u0940','\u0916\u0941\u0932\u0926\u093e\u092c\u093e\u0926','\u0935\u0948\u091c\u093e\u092a\u0942\u0930','\u0917\u0902\u0917\u093e\u092a\u0942\u0930','\u092a\u0948\u0920\u0923'],
            '\u0939\u093f\u0902\u0917\u094b\u0932\u0940':     ['\u0939\u093f\u0902\u0917\u094b\u0932\u0940','\u0938\u0947\u0928\u0917\u093e\u0935','\u0915\u0932\u092e\u0928\u0941\u0930\u0940','\u092c\u0938\u092e\u0920','\u0914\u0902\u0922\u093e \u0928\u093e\u0917\u0928\u093e\u0925'],
            '\u091c\u093e\u0932\u0928\u093e':       ['\u091c\u093e\u0932\u0928\u093e','\u092d\u094b\u0915\u0930\u0926\u0928','\u091c\u093e\u092b\u0930\u093e\u092c\u093e\u0926','\u092c\u0926\u0928\u093e\u092a\u0942\u0930','\u0905\u0902\u092c\u093e\u0921','\u0918\u0928\u0938\u093e\u0935\u0902\u0917\u0940','\u092a\u0930\u0924\u0942\u0930','\u092e\u0902\u0925\u093e'],
            '\u0928\u093e\u0902\u0926\u0947\u0921':      ['\u0928\u093e\u0902\u0926\u0947\u0921','\u0905\u0930\u094d\u0927\u093e\u092a\u0942\u0930','\u092e\u0941\u0926\u0916\u0947\u0921','\u092d\u094b\u0915\u0930','\u0909\u092e\u0930\u0940','\u0932\u094b\u0939\u093e','\u0915\u0902\u0927\u093e\u0930','\u0915\u093f\u0902\u0935\u0924','\u0939\u093f\u092e\u093e\u092f\u0924\u0928\u0917\u0930','\u0926\u0947\u0917\u0932\u0942\u0930','\u092e\u0941\u0916\u0947\u0921','\u0927\u0930\u094d\u092e\u093e\u092c\u093e\u0926','\u092c\u093f\u0932\u094b\u0932\u0940','\u0928\u093e\u092f\u0917\u093e\u0935','\u092e\u093e\u0939\u0942\u0930'],
            '\u092a\u0941\u0923\u0947':        ['\u092a\u0941\u0923\u0947 \u0938\u093f\u091f\u0940','\u0939\u0935\u0947\u0932\u0940','\u0916\u0947\u0921','\u091c\u0941\u0928\u094d\u0928\u0930','\u0906\u0902\u092c\u0947\u0917\u093e\u0935','\u092e\u093e\u0935\u0933','\u092e\u0941\u0933\u0936\u0940','\u0936\u093f\u0930\u0942\u0930','\u092a\u0941\u0930\u0902\u0927\u0930 (\u0938\u093e\u0938\u0935\u0921)','\u092c\u093e\u0930\u093e\u092e\u0924\u0940','\u0907\u0902\u0926\u093e\u092a\u0942\u0930','\u0926\u094c\u0902\u0921'],
            '\u0938\u093e\u0924\u093e\u0930\u093e':         ['\u0938\u093e\u0924\u093e\u0930\u093e','\u091c\u093e\u0913\u0932\u0940','\u0915\u094b\u0930\u0947\u0917\u093e\u0935','\u0935\u093e\u0908','\u092e\u0939\u093e\u092c\u0933\u0947\u0936\u094d\u0935\u0930','\u0916\u0902\u0921\u093e\u0933\u093e','\u092b\u0932\u091f\u0923','\u092e\u093e\u0928','\u0916\u091f\u093e\u0935','\u0935\u0947\u0932\u094d\u0939\u0947','\u092a\u093e\u091f\u0923','\u0915\u0930\u093e\u0921'],
            '\u0938\u094b\u0932\u093e\u092a\u0942\u0930':     ['\u0938\u094b\u0932\u093e\u092a\u0942\u0930 \u0928\u0949\u0930\u094d\u0925','\u092c\u093e\u0930\u0936\u0940','\u0938\u094b\u0932\u093e\u092a\u0942\u0930 \u0938\u093e\u0909\u0925','\u0905\u0915\u094d\u0915\u0932\u0915\u094b\u091f','\u092e\u0927\u093e','\u0915\u0930\u094d\u092e\u093e\u0933\u093e','\u092a\u0902\u0922\u0930\u092a\u0942\u0930','\u092e\u094b\u0939\u094b\u0933','\u092e\u093e\u0933\u0936\u093f\u0930\u0938','\u0938\u093e\u0902\u0917\u094b\u0932\u0947','\u092e\u0902\u0917\u0933\u0935\u0947\u0922\u0947'],
            '\u0938\u093e\u0902\u0917\u0932\u0940':         ['\u092e\u093f\u0930\u093e\u091c','\u0915\u0935\u0920\u0947\u092e\u0939\u093e\u0902\u0915\u093e\u0933','\u0924\u093e\u0938\u0917\u093e\u0935','\u091c\u093e\u0924','\u0935\u093e\u0932\u0935\u093e','\u0936\u093f\u0930\u093e\u0933\u093e','\u0916\u093e\u0928\u093e\u092a\u0942\u0930 (\u0935\u093f\u091f\u093e)','\u0906\u091f\u092a\u093e\u0921\u0940','\u092a\u093e\u0932\u0941\u0938'],
            '\u092c\u0940\u0921':         ['\u092c\u0940\u0921','\u091c\u093f\u092f\u094b\u0930\u0947','\u092a\u093e\u091f\u094b\u0921\u093e','\u0936\u093f\u0930\u0942\u0930-\u0915\u093e\u0938\u093e\u0930','\u0905\u0937\u094d\u091f\u0940','\u092e\u093e\u091c\u0932\u0917\u093e\u0935','\u0935\u0921\u0935\u093e\u0923\u0940','\u0915\u0948\u091c','\u0927\u093e\u0930\u0942\u0930','\u0915\u0921\u0947\u0917\u093e\u0935','\u0905\u0902\u092c\u093e\u091c\u094b\u0917\u093e\u0908','\u092a\u093e\u0930\u094d\u0932\u0940'],
            '\u0932\u093e\u0924\u0942\u0930':          ['\u0932\u093e\u0924\u0942\u0930','\u0930\u0947\u0923\u093e\u092a\u0942\u0930','\u0914\u0938\u093e','\u0905\u0939\u092e\u0926\u092a\u0942\u0930','\u091c\u0932\u0915\u094b\u091f','\u091a\u093e\u0915\u0941\u0930','\u0936\u093f\u0930\u0942\u0930 \u0905\u0928\u0902\u0924\u092a\u093e\u0933','\u0928\u093f\u0932\u0902\u0917\u093e','\u0926\u0947\u0913\u0928\u0940','\u0909\u0926\u0917\u0940\u0930'],
            '\u0927\u093e\u0930\u093e\u0936\u093f\u0935':     ['\u0927\u093e\u0930\u093e\u0936\u093f\u0935','\u0924\u0941\u0933\u091c\u093e\u092a\u0942\u0930','\u092d\u0942\u092e','\u092a\u0930\u093e\u0902\u0921\u093e','\u0935\u093e\u0936\u0940','\u0915\u0933\u0902\u092c','\u0932\u094b\u0939\u093e\u0930\u093e','\u0909\u092e\u0930\u0917\u093e'],
            '\u092a\u0930\u092d\u0923\u0940':          ['\u092a\u0930\u092d\u0923\u0940','\u0938\u094b\u0928\u092a\u0947\u0920','\u0917\u0902\u0917\u093e\u0916\u0947\u0921','\u092a\u093e\u0932\u092e','\u092a\u0942\u0930\u094d\u0923','\u0938\u0948\u0932\u0942','\u091c\u093f\u0902\u0924\u0942\u0930','\u092e\u0928\u0935\u093e\u0925','\u092a\u093e\u0925\u0930\u0940'],
            '\u091a\u0902\u0926\u094d\u0930\u092a\u0942\u0930':    ['\u091a\u0902\u0926\u094d\u0930\u092a\u0942\u0930','\u0938\u093e\u0913\u0932\u0940','\u092e\u0942\u0933','\u092c\u0932\u094d\u0932\u093e\u0930\u092a\u0942\u0930','\u092a\u094b\u0902\u092d\u0941\u0930\u094d\u0923','\u0917\u094b\u0902\u0921\u092a\u093f\u0902\u092a\u094d\u0930\u0940','\u0935\u093e\u0930\u094b\u0930\u093e','\u091a\u093f\u092e\u0942\u0930','\u092d\u0926\u094d\u0930\u093e\u0935\u0924\u0940','\u092c\u094d\u0930\u092e\u094d\u0939\u092a\u0941\u0930\u0940','\u0938\u093f\u0902\u0926\u0947\u0935\u093e\u0939\u0940','\u0930\u093e\u091c\u0941\u0930\u093e','\u0915\u094b\u0930\u092a\u0923\u093e','\u091c\u0940\u0935\u0924\u0940'],
            '\u0917\u094b\u0902\u0926\u093f\u092f\u093e':     ['\u0917\u094b\u0902\u0926\u093f\u092f\u093e','\u0917\u094b\u0930\u0947\u0917\u093e\u0935','\u0938\u093e\u0932\u0947\u0915\u0938\u093e','\u0924\u093f\u0930\u094b\u0921\u093e','\u0926\u0947\u0913\u0930\u0940','\u0905\u0930\u094d\u091c\u0941\u0928\u0940-\u092e\u094b\u0930\u0917\u093e\u0935','\u0938\u0921\u0915-\u0905\u0930\u094d\u091c\u0941\u0928\u0940','\u0906\u092e\u0917\u093e\u0935'],
            '\u0917\u0921\u091a\u093f\u0930\u094b\u0932\u0940':    ['\u0917\u0921\u091a\u093f\u0930\u094b\u0932\u0940','\u0927\u0928\u094b\u0930\u093e','\u091a\u093e\u092e\u094b\u0930\u0936\u0940','\u092e\u0941\u0932\u091a\u0947\u0930\u093e','\u0926\u0947\u0938\u093e\u0908\u0917\u0902\u091c','\u0905\u0930\u092e\u094b\u0930\u0940','\u0915\u0941\u0930\u0916\u0947\u0921\u093e','\u0915\u094b\u0930\u091a\u0940','\u0905\u0939\u0947\u0930\u0940','\u090f\u091f\u093e\u092a\u0932\u094d\u0932\u0940','\u092d\u093e\u092e\u0930\u093e\u0917\u0921','\u0938\u093f\u0930\u094b\u0902\u091a\u093e'],
            '\u0905\u0915\u094b\u0932\u093e':          ['\u0905\u0915\u094b\u0932\u093e','\u0905\u0915\u094b\u091f','\u0924\u0947\u0932\u094d\u0939\u093e\u0930\u093e','\u092c\u093e\u0932\u093e\u092a\u0942\u0930','\u092a\u093e\u0924\u0941\u0930','\u092e\u0941\u0930\u094d\u0924\u091c\u093e\u092a\u0942\u0930','\u092c\u093e\u0930\u094d\u0936\u093f\u0924\u0915\u0932\u0940'],
            '\u0905\u092e\u0930\u093e\u0935\u0924\u0940':     ['\u0905\u092e\u0930\u093e\u0935\u0924\u0940','\u092d\u091f\u0915\u0941\u0932\u0940','\u0928\u093e\u0902\u0926\u0917\u093e\u0935 \u0916\u0902\u0921\u0947\u0936\u094d\u0935\u0930','\u0927\u0930\u0923\u0940','\u091a\u093f\u0916\u0932\u0926\u0930\u093e','\u0905\u091a\u0932\u092a\u0942\u0930','\u091a\u0902\u0926\u0941\u0930\u092c\u093e\u091c\u093e\u0930','\u092e\u094b\u0930\u094d\u0936\u0940','\u0935\u093e\u0930\u0941\u0921','\u0926\u0930\u094d\u092f\u093e\u092a\u0942\u0930','\u0905\u0902\u091c\u0928\u0917\u093e\u0935-\u0938\u0941\u0930\u091c\u0940','\u091a\u0902\u0926\u0942\u0930','\u0927\u093e\u092e\u0923\u0917\u093e\u0935'],
            '\u0935\u093e\u0936\u0940\u092e':          ['\u0935\u093e\u0936\u0940\u092e','\u092e\u093e\u0932\u0947\u0917\u093e\u0935','\u0930\u093f\u0938\u094b\u0921','\u092e\u0928\u094b\u0930\u093e','\u0915\u0930\u0902\u091c\u093e','\u092e\u0902\u0917\u0930\u0942\u0933\u092a\u0940\u0930'],
            '\u092f\u0935\u0924\u092e\u093e\u0933':      ['\u092f\u0935\u0924\u092e\u093e\u0933','\u0906\u0930\u094d\u0928\u0940','\u092c\u093e\u092d\u0941\u0933\u0917\u093e\u0935','\u0915\u0933\u0902\u092c','\u0926\u0930\u094d\u0935\u094d\u0939\u093e','\u0921\u093f\u0917\u094d\u0930\u0938','\u0928\u0947\u0930','\u092a\u0941\u0938\u0926','\u0909\u092e\u0930\u0916\u0947\u0921','\u092e\u0939\u093e\u0917\u093e\u0935','\u0915\u0947\u0932\u093e\u092a\u0942\u0930','\u0930\u093e\u0933\u0947\u0917\u093e\u0935','\u0918\u091f\u0928\u091c\u0940','\u0935\u093e\u0928\u0940','\u092e\u093e\u0930\u0947\u0917\u093e\u0935','\u091d\u0930\u0940 \u091c\u092e\u093e\u0928\u0940'],
        };
        // Vibhag → array of district values that should be visible
        const vibhagDistrictMap = {
            'अहिल्यानगर':      ['अहिल्यानगर'],
            'बुलढाणा':         ['बुलढाणा'],
            'पूर्व.विदर्भ':    ['भंडारा', 'नागपूर', 'वर्धा'],
            'खानदेश':          ['धुळे', 'जळगाव', 'नंदुरबार'],
            'कोकण':            ['ठाणे', 'पालघर', 'रायगड', 'रत्नागिरी', 'सिंधुदुर्ग'],
            'कोल्हापूर':       ['कोल्हापूर'],
            'मुंबई':           ['मुंबई सिटी', 'मुंबई इतर'],
            'नाशिक':           ['नाशिक'],
            'उत्तर.मराठवाडा': ['संभाजी नगर', 'हिंगोली', 'जालना', 'नांदेड'],
            'पुणे':            ['पुणे', 'सातारा', 'सोलापूर'],
            'सांगली':          ['सांगली'],
            'दक्षिण.मराठवाडा':['बीड', 'लातूर', 'धाराशिव', 'परभणी'],
            'वनवैभव':          ['चंद्रपूर', 'गोंदिया', 'गडचिरोली'],
            'पश्चिम.विदर्भ':  ['अकोला', 'अमरावती', 'वाशीम', 'यवतमाळ'],
        };

        const vibhagSelect   = document.getElementById('pradeshik_vibhag');
        const vibhagCodeInput = document.getElementById('pradeshik_vibhag_code');
        const districtSelect  = document.getElementById('district');
        const districtCodeInput = document.getElementById('district_code');
        const talukaSelect      = document.getElementById('taluka');
        const savedTaluka       = @json(old('taluka', $sangh->taluka ?? ''));
        const pincodeAllowed    = @json(config('pincodes.allowed', []));
        const pincodeSet        = new Set(pincodeAllowed.map(String));
        const pincodeField      = document.getElementById('pincode');
        const pincodeSearch     = document.getElementById('pincode_search');
        const pincodeResults    = document.getElementById('pincode_results');
        const maleField         = document.getElementById('male');
        const femaleField       = document.getElementById('female');
        const totalMembersField = document.getElementById('total_members');

        // Collect all district options (except the blank first one) into memory once
        const allDistrictOptions = Array.from(districtSelect.options).filter(o => o.value !== '');

        function filterDistricts(selectedVibhag) {
            const allowed = vibhagDistrictMap[selectedVibhag] || null;

            // Remove all non-blank options first
            allDistrictOptions.forEach(o => o.remove());

            // Re-insert only the allowed ones (or all if nothing matched)
            const toShow = allowed
                ? allDistrictOptions.filter(o => allowed.includes(o.value))
                : allDistrictOptions;

            toShow.forEach(o => districtSelect.appendChild(o));

            // Reset district selection if current value is no longer in list
            const currentValue = districtSelect.value;
            const stillValid = toShow.some(o => o.value === currentValue);
            if (!stillValid) {
                districtSelect.value = '';
                districtCodeInput.value = '';
                populateTalukas('');
            }
        }

        function syncCode(select, input) {
            const option = select.options[select.selectedIndex];
            input.value = option ? (option.getAttribute('data-code') || '') : '';
        }

        vibhagSelect.addEventListener('change', function () {
            syncCode(vibhagSelect, vibhagCodeInput);
            filterDistricts(this.value);
        });

        function populateTalukas(sel) {
            talukaSelect.innerHTML = '<option value="">Select \u0924\u093e\u0932\u0941\u0915\u093e</option>';
            (districtTalukaMap[sel] || []).forEach(function(t) {
                var o = document.createElement('option');
                o.value = t; o.textContent = t;
                if (t === savedTaluka) o.selected = true;
                talukaSelect.appendChild(o);
            });
        }
        districtSelect.addEventListener('change', function () {
            syncCode(districtSelect, districtCodeInput);
            populateTalukas(this.value);
        });

        function renderPincodeResults(query) {
            if (!pincodeResults) return;
            const q = (query || '').trim();
            pincodeResults.innerHTML = '';
            if (q === '') { pincodeResults.style.display = 'none'; return; }
            const matches = pincodeAllowed.filter(function(pin) {
                return pin.indexOf(q) === 0 || pin.indexOf(q) > -1;
            }).slice(0, 50);
            if (!matches.length) { pincodeResults.style.display = 'none'; return; }
            matches.forEach(function(pin) {
                const li = document.createElement('li');
                li.textContent = pin;
                li.dataset.pin = pin;
                li.style.cssText = 'padding:6px 12px;cursor:pointer;font-size:14px;';
                li.addEventListener('mouseenter', function() { this.style.background = '#007bff'; this.style.color = '#fff'; });
                li.addEventListener('mouseleave', function() { this.style.background = ''; this.style.color = ''; });
                li.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    applyPincodeSelection(this.dataset.pin);
                    pincodeResults.style.display = 'none';
                });
                pincodeResults.appendChild(li);
            });
            pincodeResults.style.display = 'block';
        }

        function applyPincodeSelection(pin) {
            if (!pincodeSet.has(String(pin))) { pincodeField.value = ''; return; }
            pincodeField.value = pin;
            pincodeSearch.value = pin;
        }

        if (pincodeSearch && pincodeField && pincodeResults) {
            pincodeSearch.addEventListener('input', function () {
                const typed = this.value.replace(/\D/g, '');
                this.value = typed;
                if (typed.length === 6 && pincodeSet.has(typed)) {
                    pincodeField.value = typed;
                } else if (typed !== pincodeField.value) {
                    pincodeField.value = '';
                }
                renderPincodeResults(typed);
            });

            pincodeSearch.addEventListener('focus', function () {
                if (this.value) renderPincodeResults(this.value);
            });

            pincodeSearch.addEventListener('blur', function () {
                setTimeout(function() { pincodeResults.style.display = 'none'; }, 150);
            });

            const formEl = pincodeField.closest('form');
            if (formEl) {
                formEl.addEventListener('submit', function (e) {
                    const val = (pincodeField.value || '').trim();
                    if (val !== '' && !pincodeSet.has(val)) {
                        e.preventDefault();
                        alert('Please select a valid पिनकोड from search results.');
                        pincodeSearch.focus();
                    }
                });
            }
        }

        // Address type toggle
        const addrTypeField  = document.getElementById('address_type');
        const sectionVillage = document.getElementById('section_village_addr');
        const sectionCity    = document.getElementById('section_city_addr');
        const btnAddrVillage = document.getElementById('btn_addr_village');
        const btnAddrCity    = document.getElementById('btn_addr_city');

        function setAddrType(type) {
            if (!addrTypeField) return;
            addrTypeField.value = type;
            if (type === 'village') {
                if (sectionVillage) sectionVillage.style.display = '';
                if (sectionCity)    sectionCity.style.display    = 'none';
                if (btnAddrVillage) { btnAddrVillage.classList.remove('btn-outline-primary'); btnAddrVillage.classList.add('btn-primary'); }
                if (btnAddrCity)    { btnAddrCity.classList.remove('btn-primary');    btnAddrCity.classList.add('btn-outline-primary'); }
            } else {
                if (sectionVillage) sectionVillage.style.display = 'none';
                if (sectionCity)    sectionCity.style.display    = '';
                if (btnAddrCity)    { btnAddrCity.classList.remove('btn-outline-primary');    btnAddrCity.classList.add('btn-primary'); }
                if (btnAddrVillage) { btnAddrVillage.classList.remove('btn-primary'); btnAddrVillage.classList.add('btn-outline-primary'); }
            }
        }

        if (btnAddrVillage) btnAddrVillage.addEventListener('click', function() { setAddrType('village'); });
        if (btnAddrCity)    btnAddrCity.addEventListener('click',    function() { setAddrType('city'); });

        function sanitizeWholeNumberInput(input) {
            if (!input) return '';
            const sanitized = String(input.value || '').replace(/\D/g, '');
            input.value = sanitized;
            return sanitized;
        }

        function syncTotalMembers() {
            if (!totalMembersField) return;
            const male = sanitizeWholeNumberInput(maleField);
            const female = sanitizeWholeNumberInput(femaleField);

            if (male === '' && female === '') {
                totalMembersField.value = '';
                return;
            }

            totalMembersField.value = String((parseInt(male || '0', 10)) + (parseInt(female || '0', 10)));
        }

        ['keydown', 'input'].forEach(function(eventName) {
            [maleField, femaleField].forEach(function(field) {
                if (!field) return;
                if (eventName === 'keydown') {
                    field.addEventListener(eventName, function(e) {
                        if (['e', 'E', '+', '-', '.'].includes(e.key)) {
                            e.preventDefault();
                        }
                    });
                } else {
                    field.addEventListener(eventName, syncTotalMembers);
                }
            });
        });

        // Run on page load to reflect existing saved values
        syncCode(vibhagSelect, vibhagCodeInput);
        filterDistricts(vibhagSelect.value);
        syncCode(districtSelect, districtCodeInput);
        populateTalukas(districtSelect.value);
        syncTotalMembers();
    })();
</script>