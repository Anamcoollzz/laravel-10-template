@if (($type ?? false) === 'submit')
  <button type="submit" class="btn btn-success @if ($icon ?? false) btn-icon icon-left @endif">
    @if ($icon ?? false)
      <i class="{{ $icon }}"></i>
    @endif
    {{ $label }}
  </button>
@else
  <a class="btn btn-success @if ($icon ?? false) btn-icon btn-{{ $size ?? '' }} icon-left @endif" href="{{ $link ?? '#' }}" title="{{ $title ?? '' }}" data-toggle="tooltip">
    @if ($icon ?? false)
      <i class="{{ $icon }}"></i>
    @endif
    {{ $label ?? '' }}
  </a>
@endif
