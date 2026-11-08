@props(['id' => config('laravel-barion.pixel.id'), 'nonce' => null])
@if (is_string($id) && $id !== '')
<script @if ($nonce) nonce="{{ $nonce }}" @endif>
    window.bp = window.bp || function () { (window.bp.q = window.bp.q || []).push(arguments); };
    window.bp.l = 1 * new Date();
    (function () {
        var scriptElement = document.createElement('script');
        var firstScript = document.getElementsByTagName('script')[0];
        scriptElement.async = true;
        scriptElement.src = 'https://pixel.barion.com/bp.js';
        firstScript.parentNode.insertBefore(scriptElement, firstScript);
    })();
    window.barion_pixel_id = @js($id);
    bp('init', 'addBarionPixelId', window.barion_pixel_id);
</script>
<noscript>
    <img height="1" width="1" style="display:none" alt="Barion Pixel" src="https://pixel.barion.com/a.gif?{{ http_build_query(['ba_pixel_id' => $id, 'ev' => 'contact', 'noscript' => 1]) }}">
</noscript>
@endif
