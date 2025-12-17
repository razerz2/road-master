@php
    try {
        $colorFrom = \App\Models\SystemSetting::get('button_color_from', '#4F46E5');
        $colorTo = \App\Models\SystemSetting::get('button_color_to', '#9333EA');
    } catch (\Exception $e) {
        $colorFrom = '#4F46E5';
        $colorTo = '#9333EA';
    }
    
    // Criar versão mais escura para hover (reduzir 10% de luminosidade)
    // Cor inicial
    $hexFrom = str_replace('#', '', $colorFrom);
    $rFrom = max(0, min(255, (int)(hexdec(substr($hexFrom, 0, 2)) * 0.9)));
    $gFrom = max(0, min(255, (int)(hexdec(substr($hexFrom, 2, 2)) * 0.9)));
    $bFrom = max(0, min(255, (int)(hexdec(substr($hexFrom, 4, 2)) * 0.9)));
    $hoverFrom = '#' . str_pad(dechex($rFrom), 2, '0', STR_PAD_LEFT) . str_pad(dechex($gFrom), 2, '0', STR_PAD_LEFT) . str_pad(dechex($bFrom), 2, '0', STR_PAD_LEFT);
    
    // Cor final
    $hexTo = str_replace('#', '', $colorTo);
    $rTo = max(0, min(255, (int)(hexdec(substr($hexTo, 0, 2)) * 0.9)));
    $gTo = max(0, min(255, (int)(hexdec(substr($hexTo, 2, 2)) * 0.9)));
    $bTo = max(0, min(255, (int)(hexdec(substr($hexTo, 4, 2)) * 0.9)));
    $hoverTo = '#' . str_pad(dechex($rTo), 2, '0', STR_PAD_LEFT) . str_pad(dechex($gTo), 2, '0', STR_PAD_LEFT) . str_pad(dechex($bTo), 2, '0', STR_PAD_LEFT);
@endphp
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'primary-button-custom inline-flex items-center justify-center px-5 py-2.5 border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-wide shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transform hover:scale-105 transition-all duration-200 ease-in-out']) }} style="background: linear-gradient(to right, {{ $colorFrom }}, {{ $colorTo }});" onmouseover="this.style.background='linear-gradient(to right, {{ $hoverFrom }}, {{ $hoverTo }})'" onmouseout="this.style.background='linear-gradient(to right, {{ $colorFrom }}, {{ $colorTo }})'">
    {{ $slot }}
</button>
