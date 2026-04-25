{{-- cabinet.sites.metrics.partials.vk-pixel --}}
<div class="mb-4">
    <label class="form-label">VK Pixel ID <span class="text-danger">*</span></label>
    <input type="text" name="settings[pixel_id]" class="form-control"
           value="{{ $settings['pixel_id'] ?? '' }}"
           placeholder="Например: 1234567">
</div>
