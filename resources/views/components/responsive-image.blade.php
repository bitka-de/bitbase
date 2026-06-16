<img
    src="{{ $media->url }}"
    alt="{{ $media->alt_text ?: ($media->title ?: '') }}"
    width="{{ $media->width ?: '' }}"
    height="{{ $media->height ?: '' }}"
    loading="{{ $loading }}"
    decoding="async"
    class="{{ $class }}"
>
