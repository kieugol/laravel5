@extends($layout)

@section('content')
    @include('report.module_filter')
<section class="content">
    <div id="box-report" class="bg-white p20">
        @include('report.header')
        <table class="table pad5 no-border">
            <thead>
                <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                    <th class="bold" style="vertical-align: middle">Code</th>
                    <th class="bold" style="vertical-align: middle" class="bold">Items</th>
                    <th class="text-right bold">00:00<br>09:59</th>
                    <th class="text-right bold">10:00<br>10:59</th>
                    <th class="text-right bold">11:00<br>11:59</th>
                    <th class="text-right bold">12:00<br>12:59</th>
                    <th class="text-right bold">13:00<br>13:59</th>
                    <th class="text-right bold">14:00<br>14:59</th>
                    <th class="text-right bold">15:00<br>15:59</th>
                    <th class="text-right bold">16:00<br>16:59</th>
                    <th class="text-right bold">17:00<br>17:59</th>
                    <th class="text-right bold">18:00<br>18:59</th>
                    <th class="text-right bold">19:00<br>19:59</th>
                    <th class="text-right bold">20:00<br>20:59</th>
                    <th class="text-right bold">21:00<br>21:59</th>
                    <th class="text-right bold">22:00<br>22:59</th>
                    <th class="text-right bold">23:00<br>23:59</th>
                    <th class="text-right bold" style="vertical-align: middle">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data as $category): ?>
                    <?php foreach ($category['groups'] as $group): ?>
                        <?php foreach ($group['menus'] as $item): ?><tr>
                                <td>{{ $item['sku'] }}</td>
                                <td>{{ $item['name'] }}</td>
                                <td class="text-right">{{ $item[9] }}</td>
                                <td class="text-right">{{ $item[10] }}</td>
                                <td class="text-right">{{ $item[11] }}</td>
                                <td class="text-right">{{ $item[12] }}</td>
                                <td class="text-right">{{ $item[13] }}</td>
                                <td class="text-right">{{ $item[14] }}</td>
                                <td class="text-right">{{ $item[15] }}</td>
                                <td class="text-right">{{ $item[16] }}</td>
                                <td class="text-right">{{ $item[17] }}</td>
                                <td class="text-right">{{ $item[18] }}</td>
                                <td class="text-right">{{ $item[19] }}</td>
                                <td class="text-right">{{ $item[20] }}</td>
                                <td class="text-right">{{ $item[21] }}</td>
                                <td class="text-right">{{ $item[22] }}</td>
                                <td class="text-right">{{ $item[23] }}</td>
                                <td class="text-right">{{ $item['total'] }}</td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($group['addon_name'] != ""): ?>
                            <tr class="bold bg-info">
                                <td></td>
                                <td>{{ $group['name'] }}</td>
                                <td class="text-right">{{ $group[9] }}</td>
                                <td class="text-right">{{ $group[10] }}</td>
                                <td class="text-right">{{ $group[11] }}</td>
                                <td class="text-right">{{ $group[12] }}</td>
                                <td class="text-right">{{ $group[13] }}</td>
                                <td class="text-right">{{ $group[14] }}</td>
                                <td class="text-right">{{ $group[15] }}</td>
                                <td class="text-right">{{ $group[16] }}</td>
                                <td class="text-right">{{ $group[17] }}</td>
                                <td class="text-right">{{ $group[18] }}</td>
                                <td class="text-right">{{ $group[19] }}</td>
                                <td class="text-right">{{ $group[20] }}</td>
                                <td class="text-right">{{ $group[21] }}</td>
                                <td class="text-right">{{ $group[22] }}</td>
                                <td class="text-right">{{ $group[23] }}</td>
                                <td class="text-right">{{ $group['total'] }}</td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <tr class="bold bg-primary">
                        <td>TOTAL</td>
                        <td>{{ $category['name'] }}</td>
                        <td class="text-right">{{ $category[9] }}</td>
                        <td class="text-right">{{ $category[10] }}</td>
                        <td class="text-right">{{ $category[11] }}</td>
                        <td class="text-right">{{ $category[12] }}</td>
                        <td class="text-right">{{ $category[13] }}</td>
                        <td class="text-right">{{ $category[14] }}</td>
                        <td class="text-right">{{ $category[15] }}</td>
                        <td class="text-right">{{ $category[16] }}</td>
                        <td class="text-right">{{ $category[17] }}</td>
                        <td class="text-right">{{ $category[18] }}</td>
                        <td class="text-right">{{ $category[19] }}</td>
                        <td class="text-right">{{ $category[20] }}</td>
                        <td class="text-right">{{ $category[21] }}</td>
                        <td class="text-right">{{ $category[22] }}</td>
                        <td class="text-right">{{ $category[23] }}</td>
                        <td class="text-right">{{ $category['total'] }}</td>
                    </tr>
                <?php endforeach; ?>
                <tr class="bold t15" style="background:lightgray">
                    <td colspan="2">GRAND TOTAL</td>
                    <td class="text-right">{{ $grand[9] }}</td>
                    <td class="text-right">{{ $grand[10] }}</td>
                    <td class="text-right">{{ $grand[11] }}</td>
                    <td class="text-right">{{ $grand[12] }}</td>
                    <td class="text-right">{{ $grand[13] }}</td>
                    <td class="text-right">{{ $grand[14] }}</td>
                    <td class="text-right">{{ $grand[15] }}</td>
                    <td class="text-right">{{ $grand[16] }}</td>
                    <td class="text-right">{{ $grand[17] }}</td>
                    <td class="text-right">{{ $grand[18] }}</td>
                    <td class="text-right">{{ $grand[19] }}</td>
                    <td class="text-right">{{ $grand[20] }}</td>
                    <td class="text-right">{{ $grand[21] }}</td>
                    <td class="text-right">{{ $grand[22] }}</td>
                    <td class="text-right">{{ $grand[23] }}</td>
                    <td class="text-right">{{ $grand['total'] }}</td>
                </tr>
            </tbody>
        </table>

        <?php echo view("report.footer"); ?>
    </div>
</section>
<style>@media print{@page {size: landscape}}</style>
@endsection