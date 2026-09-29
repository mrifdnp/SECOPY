<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @if(!empty($rotate))
        @page {
            size: {{ ($labelHeight ?? 15) + ($gapV ?? 2) + ($marginTop ?? 0) + 1 }}mm {{ $paperWidth ?? 70 }}mm;
            margin-top: {{ $marginTop ?? 5 }}mm;
            margin-left: {{ $marginLeft ?? 5 }}mm;
            margin-right: 0;
            margin-bottom: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        @else
        @page {
            size: {{ $paperWidth ?? 70 }}mm {{ ($labelHeight ?? 15) + ($gapV ?? 2) + ($marginTop ?? 0) + 1 }}mm;
            margin-top: {{ $marginTop ?? 5 }}mm;
            margin-left: {{ $marginLeft ?? 5 }}mm;
            margin-right: 0;
            margin-bottom: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        @endif
        .label {
            width: {{ $labelWidth ?? 52 }}mm;
            height: {{ $labelHeight ?? 30 }}mm;
            margin-right: {{ $gapH ?? 2 }}mm;
            margin-bottom: {{ $gapV ?? 2 }}mm;
            float: left;
            box-sizing: border-box;
            overflow: hidden;
            position: relative;
            page-break-inside: avoid;
            /* Optional border for debugging: border: 0.5px dashed #ccc; */
        }
        .clear {
            clear: both;
        }
        .page-break {
            page-break-after: always;
            clear: both;
        }
        .barcode-wrapper {
            white-space: nowrap;
        }
        .barcode-wrapper svg, .barcode-wrapper img {
            @if(($barcodeType ?? 'barcode') === 'qrcode')
                height: {{ $barcodeHeight ?? 25 }}px !important;
                width: {{ $barcodeHeight ?? 25 }}px !important;
            @else
                height: {{ $barcodeHeight ?? 25 }}px !important;
                width: 100% !important;
                max-width: 28mm !important;
            @endif
        }
        .text-sku {
            font-size: {{ ($fontSize ?? 5) - 1 }}pt;
            font-weight: bold;
            text-align: center;
            width: 100%;
            overflow: hidden;
            margin-top: 1mm;
        }
        .text-name {
            font-size: 6pt;
            font-weight: bold;
            line-height: 1.1;
            text-align: left;
            width: 100%;
            height: 5.5mm;
            overflow: hidden;
            margin-bottom: 0mm;
        }
        .text-variant {
            font-weight: bold;
        }
        .bottom-row {
            width: 100%;
            border-collapse: collapse;
        }
        .bottom-row td {
            vertical-align: middle;
            padding: 0;
        }
        .barcode-wrapper {
            text-align: left;
        }
        .text-price {
            font-size: {{ ($fontSize ?? 5) + 2.5 }}pt;
            font-weight: bold;
            text-align: left;
            white-space: nowrap;
            background-color: black;
            color: white;
            display: inline-block;
            padding: 0.5mm 1mm;
            border-radius: 0.5mm;
        }
    </style>
</head>
<body>
    @php
        $count = 0;
        $total = count($productDetails);
        $itemsPerPage = ($columns ?? 5) * ($rowsPerPage ?? 7);
    @endphp

    @foreach ($productDetails as $index => $productDetail)
        <div class="label">
            <div style="padding: 0.5mm 1mm; height: 100%; box-sizing: border-box; overflow: hidden;">
                @if ($includeProductName ?? true)
                    @php
                        $title = $productDetail->productWithoutScope->title ?? '';
                        $titleLength = strlen($title);
                        $titleFontSize = $titleLength > 45 ? '5.5pt' : ($titleLength > 35 ? '6.5pt' : ($titleLength > 16 ? '7.5pt' : '9pt'));
                        $titleLineHeight = '1';
                    @endphp
                    <div class="text-name" style="font-size: {{ $titleFontSize }}; line-height: {{ $titleLineHeight }};">
                        {{ $title }}
                    </div>
                @endif

                <table class="bottom-row">
                    <tr>
                        <td style="width: 1%; white-space: nowrap;">
                            @if ($includeBarcode ?? true)
                                <div class="barcode-wrapper">
                                    @php
                                        if (($barcodeType ?? 'barcode') === 'qrcode') {
                                            $qrSvg = \SimpleSoftwareIO\QrCode\Facades\QrCode::size($barcodeWidth ?? 40)->margin(0)->generate($productDetail->sku);
                                            $barcodeOutput = '<img src="data:image/svg+xml;base64,' . base64_encode($qrSvg) . '" />';
                                        } else {
                                            $barcode = $barcodeGenerator->getBarcode($productDetail->sku);
                                            
                                            $bw = $barcodeWidth ?? 2;
                                            if ($bw > 10) {
                                                $bw = 2; // Clamp
                                            }
                                            $totalWidth = $barcode->getWidth() * $bw;
                                            
                                            $pngRenderer = new \Picqer\Barcode\Renderers\PngRenderer();
                                            $pngData = $pngRenderer->render($barcode, $totalWidth, $barcodeHeight ?? 40);
                                            $barcodeOutput = '<img src="data:image/png;base64,' . base64_encode($pngData) . '" />';
                                        }
                                    @endphp
                                    {!! $barcodeOutput !!}
                                </div>
                            @endif
                        </td>
                        <td style="text-align: left; padding-left: 2mm;">
                            @if ($includePrice ?? true)
                                <div class="text-price">
                                    {{ 'IDR ' . number_format($productDetail->price, 0, ',', '.') }}
                                </div>
                            @endif
                            @if (($includeVariation ?? true) && ($productDetail->variant || $productDetail->color))
                                <div style="font-size: 4.5pt; margin-top: 0.5mm; text-align: left; line-height: 1.1;" class="text-variant">
                                    @if ($productDetail->variant)
                                        {{ $productDetail->variant->name }}
                                    @endif
                                    @if ($productDetail->variant && $productDetail->color)
                                        -
                                    @endif
                                    @if ($productDetail->color)
                                        {{ $productDetail->color->label }}
                                    @endif
                                </div>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        @php
            $count++;
            if ($count % $itemsPerPage == 0 && $index < $total - 1) {
                echo '<div class="page-break"></div>';
            } elseif ($count % ($columns ?? 5) == 0 && $index < $total - 1) {
                echo '<div class="clear"></div>';
            }
        @endphp
    @endforeach
</body>
</html>
