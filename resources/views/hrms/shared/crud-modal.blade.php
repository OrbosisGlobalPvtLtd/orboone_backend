<div class="modal fade orb-modal" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form method="POST" action="{{ $action }}" class="modal-content">
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif
            <div class="modal-header">
                <div>
                    <h5 class="modal-title">{{ $modalTitle }}</h5>
                    <small style="color:rgba(255,255,255,.8)">Changes are saved immediately after validation.</small>
                </div>
                <button type="button" class="close btn-close" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"><span>&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    @foreach($fields as $field)
                        @php
                            $name = $field['name'];
                            $value = old($name, $row ? data_get($row, $name) : ($field['default'] ?? null));
                        @endphp
                        <div class="col-md-{{ $field['col'] ?? 6 }} mb-3">
                            @if(($field['type'] ?? 'text') === 'checkbox')
                                <label class="d-block">&nbsp;</label>
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="{{ $modalId }}_{{ $name }}" name="{{ $name }}" value="1" {{ $value ? 'checked' : '' }}>
                                    <label class="custom-control-label font-weight-bold" for="{{ $modalId }}_{{ $name }}">{{ $field['label'] }}</label>
                                </div>
                            @else
                                <label class="orb-form-label">{{ $field['label'] }}</label>
                                @if(($field['type'] ?? 'text') === 'select')
                                    <select name="{{ $name }}" class="form-control">
                                        <option value="">{{ $field['placeholder'] ?? 'Select' }}</option>
                                        @foreach($field['options'] as $optionValue => $optionLabel)
                                            <option value="{{ $optionValue }}" {{ (string) $value === (string) $optionValue ? 'selected' : '' }}>{{ $optionLabel }}</option>
                                        @endforeach
                                    </select>
                                @elseif(($field['type'] ?? 'text') === 'textarea')
                                    <textarea name="{{ $name }}" class="form-control" rows="3">{{ $value }}</textarea>
                                @else
                                    <input type="{{ $field['type'] ?? 'text' }}" name="{{ $name }}" value="{{ $value }}" class="form-control" placeholder="{{ $field['placeholder'] ?? '' }}">
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn orb-btn orb-btn-light" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button class="btn orb-btn orb-btn-gradient"><i class="fas fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>
