<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <form method="POST" action="{{ $action }}" class="modal-content orb-modal">
            @csrf
            @if($method !== 'POST')
                @method($method)
            @endif

            <div class="orb-modal-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="modal-title">{{ $modalTitle }}</h5>
                    <p class="orb-modal-subtitle">Changes are saved immediately after validation.</p>
                </div>
                <button type="button" class="orb-modal-close-btn" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="width: 32px; height: 32px; border-radius: 50%; background: rgba(255,255,255,0.22); display: inline-flex; align-items: center; justify-content: center; border: 0; color: #ffffff !important; font-size: 14px; opacity: 1; outline: none; cursor: pointer; padding: 0; margin: 0; line-height: 1; transition: background 0.2s ease, transform 0.15s ease;" onmouseover="this.style.background='rgba(255,255,255,0.38)'; this.style.transform='scale(1.05)';" onmouseout="this.style.background='rgba(255,255,255,0.22)'; this.style.transform='scale(1)';">
                    <i class="fas fa-times" style="color: #ffffff; font-size: 14px; line-height: 1;"></i>
                </button>
            </div>

            <div class="modal-body orb-modal-body">
                <div class="orb-form-section">
                    <div class="orb-form-section-title">
                        <i class="fas fa-edit"></i> Form Details
                    </div>
                    <div class="row">
                        @foreach($fields as $field)
                            @php
                                $name = $field['name'];
                                $value = old($name, $row ? data_get($row, $name) : ($field['default'] ?? ($field['value'] ?? null)));
                                if ($value === null && !empty($field['options']) && count($field['options']) === 1 && $name === 'employee_id') {
                                    $value = array_key_first($field['options']);
                                }
                            @endphp
                            <div class="col-md-{{ $field['col'] ?? 6 }} mb-3">
                                @if(($field['type'] ?? 'text') === 'checkbox')
                                    <label class="d-block">&nbsp;</label>
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="{{ $modalId }}_{{ $name }}" name="{{ $name }}" value="1" {{ $value ? 'checked' : '' }}>
                                        <label class="custom-control-label font-weight-bold" for="{{ $modalId }}_{{ $name }}">{{ $field['label'] }}</label>
                                    </div>
                                @else
                                @if(($field['type'] ?? 'text') === 'select')
                                    <x-form.select 
                                        :name="$name"
                                        :label="$field['label']"
                                        :options="$field['options'] ?? []"
                                        :selected="$value"
                                        :placeholder="$field['placeholder'] ?? 'Select '.$field['label']"
                                        searchable="true"
                                        :required="!empty($field['required'])"
                                    />
                                @elseif(($field['type'] ?? 'text') === 'date')
                                    <div class="orb-form-group mb-0">
                                        <label class="orb-form-label">
                                            {{ $field['label'] }}
                                            @if(!empty($field['required']))
                                                <span class="text-danger">*</span>
                                            @endif
                                        </label>
                                        <x-form.date-picker 
                                            :name="$name"
                                            :value="$value"
                                            :placeholder="$field['placeholder'] ?? 'dd-mm-yyyy'"
                                            :required="!empty($field['required'])"
                                        />
                                    </div>
                                    @error($name)
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                @elseif(($field['type'] ?? 'text') === 'textarea')
                                    <label class="orb-form-label">{{ $field['label'] }}</label>
                                    <textarea name="{{ $name }}" class="form-control" rows="3">{{ $value }}</textarea>
                                    @error($name)
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                @else
                                    <label class="orb-form-label">{{ $field['label'] }}</label>
                                    <input type="{{ $field['type'] ?? 'text' }}" name="{{ $name }}" value="{{ $value }}" class="form-control" placeholder="{{ $field['placeholder'] ?? '' }}">
                                    @error($name)
                                        <small class="text-danger d-block mt-1">{{ $message }}</small>
                                    @enderror
                                @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="modal-footer orb-modal-footer">
                <button type="button" class="orb-btn-light" data-dismiss="modal" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="orb-btn-primary"><i class="fas fa-save"></i> Save</button>
            </div>
        </form>
    </div>
</div>
