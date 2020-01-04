@extends($layout)

@section('content')
    @include('report.module_filter')
    <section class="content">
        <div id="box-report" class="bg-white p20">
            @include('report.header')
            <table class="table table-bordered" id="material-usage">
                <thead>
                <tr>
                    <th class="text-left">OrderID</th>
                    <th class="text-left">Sku</th>
                    <th class="text-right">Qty</th>
                    <th class="text-left">Code</th>
                    <th class="text-left">Name</th>
                    <th class="text-right">Usage</th>
                    <th class="text-left">Unit</th>
                    <th class="text-left">Price</th>
                    <th class="text-left">Total</th>
                    <th class="text-left">Cost SKU Menu</th>
                    <th class="text-left">Cost Order</th>
                    <th class="text-center">Order Date</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $item): ?>
                <tr>
                    <?php if(isset($item->rowspan_order_id)) : ?>
                    <td class="text-left rowspanned cell" rowspan="{{ $item->rowspan_order_id }}">{{ $item->order_number }}</td>
                    <?php endif; ?>
                    <?php if(isset($item->rowspan_sku)) : ?>
                    <td class="text-left rowspanned cell" rowspan="{{ $item->rowspan_sku }}">{{ $item->sku }} - {{ $item->sku_name }}</td>
                    <td class="text-right rowspanned cell" rowspan="{{ $item->rowspan_sku }}">{{ $item->quantity }}</td>
                    <?php endif; ?>

                    <td class="text-left {{ $item->highlight }}">{{ $item->product_code }}</td>
                    <td class="text-left {{ $item->highlight }}">{{ $item->product_name }}</td>
                    <td class="text-right {{ $item->highlight }}">{{ number_format((float)$item->usage, 2, '.', 0) }}</td>
                    <td class="text-left {{ $item->highlight }}">{{ $item->usage_unit }}</td>
                    <td class="text-left {{ $item->highlight }}">{{ number_format($item->price, 2) }}</td>
                    <td class="text-left {{ $item->highlight }}">{{ number_format($item->total, 2) }}</td>
                    <?php if(isset($item->rowspan_sku)) : ?>
                    <td class="text-left" rowspan="{{ $item->rowspan_sku }}">{{ number_format($item->cost_sku, 2) }}</td>
                    <?php endif; ?>
                    <?php if(isset($item->rowspan_order_id)) : ?>
                    <td class="text-left"
                        rowspan="{{ $item->rowspan_order_id }}">{{ number_format($item->cost_order, 2) }}</td>
                    <?php endif; ?>
                    <td class="text-center">{{ $item->created_date }}</td>
                </tr>
                <?php endforeach; ?>

                </tbody>
            </table>

            <?php echo view("report.footer"); ?>
        </div>
    </section>
    <script>
        $(document).ready(function () {
            $(".select2").select2();
        });
    </script>
    <style>
        @media print {
            @page {
                size: landscape
            }
        }

        .light-orange {
            background-color: lightsalmon;
        }

        .light-green {
            background-color: lightgreen;
        }
    </style>


@endsection
