@extends($layout)

@section('content')
@include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')
        <table class="table pad5 no-border">
            <thead>
                <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                    <th class="bold" rowspan="2" style="border:1px solid lightgray; vertical-align: middle">item</th>
                    <th class="bold" rowspan="2" style="border:1px solid lightgray; vertical-align: middle">description</th>
                    <th class="text-center bold" colspan="2" style="border:1px solid lightgray">Eat In</th>
                    <th class="text-center bold" colspan="2" style="border:1px solid lightgray">Takeaway</th>
                    <th class="text-center bold" colspan="2" style="border:1px solid lightgray">Delivery</th>
                    <th class="text-center bold" colspan="2" style="border:1px solid lightgray">Total</th>
                </tr>
                <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                    <th class="text-right bold" style="border:1px solid lightgray">Quantity</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Sales</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Quantity</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Sales</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Quantity</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Sales</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Quantity</th>
                    <th class="text-right bold" style="border:1px solid lightgray">Sales</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $category): ?>
                    <?php if (!empty($category['menus'])): ?>
                        <?php
                        $addon_name = null;
                        $temp = null;
                        $group = array(
                            'addon_name' => null,
                            'I' => array('quantity' => 0, 'amount' => 0),
                            'D' => array('quantity' => 0, 'amount' => 0),
                            'C' => array('quantity' => 0, 'amount' => 0),
                            'total' => array('quantity' => 0, 'amount' => 0)
                        );
                        ?>
                        <?php foreach ($category['menus'] as $k => $group): ?>

                            <?php foreach ($group['menus'] as $menu): ?>
                                <tr>
                                    <td class="">{{ isset($menu['sku']) ? $menu['sku'] : "" }}</td>
                                    <td class="">{{ $menu['name'] }}</td>
                                    <td class="text-right">{{ $menu['I']['quantity'] }}</td>
                                    <td class="text-right">{{ $menu['I']['amount_format'] }}</td>
                                    <td class="text-right">{{ $menu['C']['quantity'] }}</td>
                                    <td class="text-right">{{ $menu['C']['amount_format'] }}</td>
                                    <td class="text-right">{{ $menu['D']['quantity'] }}</td>
                                    <td class="text-right">{{ $menu['D']['amount_format'] }}</td>
                                    <td class="text-right">{{ $menu['total']['quantity'] }}</td>
                                    <td class="text-right">{{ $menu['total']['amount_format'] }}</td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($k != ""): ?>
                                <tr class="bg-info bold">
                                    <td class=""></td>
                                    <td class="">{{ $group['name'] }}</td>
                                    <td class="text-right">{{ $group['I']['quantity'] }}</td>
                                    <td class="text-right">{{ $group['I']['amount_format'] }}</td>
                                    <td class="text-right">{{ $group['C']['quantity'] }}</td>
                                    <td class="text-right">{{ $group['C']['amount_format'] }}</td>
                                    <td class="text-right">{{ $group['D']['quantity'] }}</td>
                                    <td class="text-right">{{ $group['D']['amount_format'] }}</td>
                                    <td class="text-right">{{ $group['total']['quantity'] }}</td>
                                    <td class="text-right">{{ $group['total']['amount_format'] }}</td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <tr class="bg-primary">
                            <td class="bold" colspan="2"><span style="text-decoration: underline">{{ $category['name'] }}</span></td>
                            <td class="text-right bold">{{ $category['I']['quantity'] }}</td>
                            <td class="text-right bold">{{ $category['I']['amount_format'] }}</td>
                            <td class="text-right bold">{{ $category['C']['quantity'] }}</td>
                            <td class="text-right bold">{{ $category['C']['amount_format'] }}</td>
                            <td class="text-right bold">{{ $category['D']['quantity'] }}</td>
                            <td class="text-right bold">{{ $category['D']['amount_format'] }}</td>
                            <td class="text-right bold">{{ $category['total']['quantity'] }}</td>
                            <td class="text-right bold">{{ $category['total']['amount_format'] }}</td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <tr><td colspan="9"></td></tr>
                <tr class="bg-danger t15" style="background: lightgray">
                    <td class="bold" colspan="2"><span style="text-decoration: underline">GRAND TOTAL</span></td>
                    <td class="text-right bold">{{ $grand['I']['quantity'] }}</td>
                    <td class="text-right bold">{{ $grand['I']['amount_format'] }}</td>
                    <td class="text-right bold">{{ $grand['C']['quantity'] }}</td>
                    <td class="text-right bold">{{ $grand['C']['amount_format'] }}</td>
                    <td class="text-right bold">{{ $grand['D']['quantity'] }}</td>
                    <td class="text-right bold">{{ $grand['D']['amount_format'] }}</td>
                    <td class="text-right bold">{{ $grand['total']['quantity'] }}</td>
                    <td class="text-right bold">{{ $grand['total']['amount_format'] }}</td>
                </tr>
            </tbody>
        </table>

        <?php echo view("report.footer"); ?>
    </div>
</section>
<style>@media print{@page {size: landscape}}</style>
@endsection