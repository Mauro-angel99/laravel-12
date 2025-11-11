@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-copam-blue focus:ring-copam-blue rounded-md shadow-sm']) }}>
