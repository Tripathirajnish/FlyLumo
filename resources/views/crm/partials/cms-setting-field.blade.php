<div class="cms2-field {{ $s->type==='textarea' || $s->type==='image' ? 'wide':'' }}" data-setting-search="{{ strtolower(($s->label ?: '').' '.$s->key.' '.$s->value) }}">
    <div class="cms2-label-row">
        <label>{{ $s->label ?: $s->key }}</label>
        <code class="cms2-dev-key">{{ $s->key }}</code>
    </div>
    @if($s->type==='toggle')
        <label class="cms2-toggle">
            <input type="checkbox" name="settings[{{ $s->key }}]" value="1" @checked($s->value==='1')>
            <span class="track"><span class="knob"></span></span><b>{{ $s->value==='1'?'Visible':'Hidden' }}</b>
        </label>
    @elseif($s->type==='textarea')
        <textarea class="form-control cms2-input" rows="3" name="settings[{{ $s->key }}]">{{ $s->value }}</textarea>
    @elseif($s->type==='color')
        <div class="cms2-color"><input type="color" value="{{ $s->value }}" oninput="this.nextElementSibling.value=this.value"><input class="form-control cms2-input" name="settings[{{ $s->key }}]" value="{{ $s->value }}"></div>
    @elseif($s->type==='image')
        <div class="cms2-image-field"><div><label class="cms2-upload"><i class="fa-solid fa-cloud-arrow-up"></i><span><b>{{ $s->value ? 'Replace image' : 'Upload image' }}</b><small>JPG, JPEG, PNG or WEBP · Max 5 MB</small></span><input type="file" name="images[{{ $s->key }}]" accept=".jpg,.jpeg,.png,.webp"></label></div>@if($s->value)<div class="cms2-image-preview" style="background-image:url('{{ $s->value }}')"><span>Current image</span></div>@endif</div>
    @else
        <div class="cms2-input-wrap"><input class="form-control cms2-input" name="settings[{{ $s->key }}]" value="{{ $s->value }}"><button type="button" class="cms2-copy" title="Copy value"><i class="fa-regular fa-copy"></i></button></div>
    @endif
</div>
