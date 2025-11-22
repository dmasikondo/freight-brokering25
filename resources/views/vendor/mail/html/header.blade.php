@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
@if (trim($slot) === 'Transpartner::Logistics')
<img src="https://transpartnerlogistics.co.zw/storage/img/tpl_logo.jpg" class="logo" alt="Transpartner Logistics Logo">
@else
{!! $slot !!}
@endif
</a>
</td>
</tr>
               