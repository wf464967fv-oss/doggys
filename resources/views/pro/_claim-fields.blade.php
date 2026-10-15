{{-- Ownership proof fields shared by the claim and "add your business" forms (multipart form). --}}
<div class="grid gap-5 sm:grid-cols-2">
    <x-input name="bce_number" :label="__('pro.fields.bce_number')" :hint="__('pro.hints.bce_number')" placeholder="0123.456.789"
        inputmode="numeric" autocomplete="off" maxlength="20" required />
    <x-input name="position" :label="__('pro.fields.position')" :hint="__('pro.hints.position')" autocomplete="organization-title" maxlength="100" required />
</div>
<x-input name="proof" type="file" :label="__('pro.fields.proof')" :hint="__('pro.hints.proof')"
    accept=".pdf,.jpg,.jpeg,.png,application/pdf,image/jpeg,image/png" required
    class="[&_input]:cursor-pointer [&_input]:file:mr-3 [&_input]:file:rounded-full [&_input]:file:border-0 [&_input]:file:bg-cream [&_input]:file:px-4 [&_input]:file:py-2 [&_input]:file:font-bold [&_input]:file:text-brown" />
<x-textarea name="message" :label="__('pro.fields.message')" :hint="__('pro.hints.message')" rows="3" maxlength="2000" optional />
