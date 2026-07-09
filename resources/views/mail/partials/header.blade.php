<tr>
    <td style="background:#2ba99b;padding:26px 32px;" align="left">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
                @if($tvrtka?->logo)
                    <td style="width:52px;vertical-align:middle;">
                        <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($tvrtka->logo) }}"
                             alt="{{ $tvrtka->naziv }}"
                             style="max-height:44px;max-width:110px;display:block;">
                    </td>
                @endif
                <td style="vertical-align:middle;{{ $tvrtka?->logo ? 'padding-left:16px;' : '' }}">
                    <span style="color:#ffffff;font-size:18px;font-weight:700;font-family:Arial,Helvetica,sans-serif;">{{ $tvrtka?->naziv ?? 'plačko.app' }}</span>
                </td>
            </tr>
        </table>
    </td>
</tr>
