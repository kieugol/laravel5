@extends($layout)

@section('content')
    <section class="content-header">
        <div class="row">
            <div class="col-md-9">
                <form action="<?php echo url()->current() ?>" method="get" pjax-container>
                    <div class="form-horizontal">
                        <div class="form-group">
                            @if(isset($master_types))
                                <div class="pull-left ml15">
                                    <select name="type" class="form-control select2" style="width:200px">
                                        <option value="">Select Type</option>
                                        @foreach ($master_types as $type)
                                            <option value="{{$type->id}}" <?php echo request("type") == $type->id ? "selected" : "" ?>>{{$type->name}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <div class="col-sm-6" style="margin-right:-15px">
                                <input type="text" name="search_value" class="form-control"
                                       value="<?php echo request("search_value") ? request("search_value") : "" ?>"
                                       placeholder="Search..">
                            </div>
                            <button type="submit" class="btn btn-primary pull-left"><i class="fa fa-filter"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="col-md-3">
                <div class="pull-right">
                    @if ($base_url_download)
                        <div class="btn-group pull-right ml10">
                            <a class="btn btn-twitter"><i class="fa fa-download"></i> Export</a>
                            <button type="button" class="btn btn-twitter dropdown-toggle" data-toggle="dropdown"
                                    aria-expanded="false">
                                <span class="caret"></span>
                                <span class="sr-only">Toggle Dropdown</span>
                            </button>
                            <ul class="dropdown-menu" role="menu">
                                <li><a href="#" onclick="download_report('{{$base_url_download}}', 'excel')"
                                       target="_blank">Excel</a></li>
                                <li><a href="#" onclick="download_report('{{$base_url_download}}', 'csv')"
                                       target="_blank">CSV</a>
                                </li>
                            </ul>
                        </div>
                    @endif
                    <button class="btn btn-info" onclick="print_element('#box-report')"><i
                                class="fa fa-print"></i> Print
                    </button>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div id="box-report" class="bg-white p20">
            @include('report.header')
            <form class="form-inline">
                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs" role="tablist">
                            <li role="presentation" class="active"><a href="#material" aria-controls="material"
                                                                      role="tab" data-toggle="tab">Material</a></li>
                            <li role="presentation"><a href="#recipe" aria-controls="recipe" role="tab"
                                                       data-toggle="tab">Recipe</a></li>
                        </ul>
                        <div class="tab-content">
                            <div role="tabpanel" class="tab-pane fade in active" id="material">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th class="text-left">Material Code</th>
                                        <th class="text-left">Material Name</th>
                                        <th class="text-right">Qty Recipe</th>
                                        <th class="text-left">Recipe Unit</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($materials as $item): ?>
                                    <tr>
                                        <td class="text-left">{{ $item->material_code }}</td>
                                        <td class="text-left">{{ $item->material_name }}</td>
                                        <td class="text-right recipe_qty">{{ $item->quantity_recipe }}</td>
                                        <td class="text-left">{{ $item->recipe_uom }}</td>
                                    </tr>
                                    <?php endforeach; ?>

                                    </tbody>
                                </table>
                            </div>
                            <div role="tabpanel" class="tab-pane fade" id="recipe">
                                <table class="table table-hover">
                                    <thead>
                                    <tr>
                                        <th class="text-left">Recipe Code</th>
                                        <th class="text-left">Recipe Name</th>
                                        <th class="text-right">Qty Recipe</th>
                                        <th class="text-left">Recipe Unit</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($recipes as $recipe): ?>
                                    <tr>
                                        <td class="text-left">{{ $recipe->recipe_code }}</td>
                                        <td class="text-left">{{ $recipe->name }}</td>
                                        <td class="text-right">{{ $recipe->quantity }}</td>
                                        <td class="text-left">{{ $recipe->uom }}</td>
                                    </tr>
                                    <?php endforeach; ?>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <?php echo view("report.footer"); ?>
        </div>
    </section>
    <script>
        $(document).ready(function () {
            $(".select2").select2();
        });

        $('.conversion').on('change', function () {
            var conversion = $(this).val();
            var outlet_uom = $('option:selected', this).attr('outlet-uom');

            if (conversion != '') {
                $(this).closest('tr').find('.outlet_qty').text(conversion);
                $(this).closest('tr').find('.outlet_unit').text(outlet_uom);
            } else {
                $(this).closest('tr').find('.outlet_qty').text('0');
                $(this).closest('tr').find('.outlet_unit').text('Gram');
            }
        });
        $('.recipe_qty').each(function () {
            var value = $(this).text();
            if (!value) {
                value = 0;
            }
            var add_digit = toCurrency(parseFloat(value));
            $(this).text(add_digit);
        });

    </script>
    <style>
        @media print {
            @page {
                size: landscape
            }
        }
    </style>


@endsection
