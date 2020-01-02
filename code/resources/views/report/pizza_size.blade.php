@extends($layout)
@section('content')
    @include('report.module_filter')
    <section class="content">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <div id="box-report" class="bg-white p20">
                    <div class="row">
                        @include('report.header')
                    </div>
                    <table class="table no-border">
                        <thead>
                        <tr>
                            <th>PIZZA SIZE</th>
                            <th class="text-center">QTY</th>
                            <th class="text-center">PERCENTAGE</th>
                        </tr>
                        </thead>
                        <tbody>

                        <tr>
                            <td><strong>Jumbo</strong></td>
                            <td class="text-center">{{ isset($jumbo['qty']) ? $jumbo['qty'] : 0 }}</td>
                            <td class="text-center">{{ isset($jumbo['percentage']) ? $jumbo['percentage'] : 0 }} %</td>
                        </tr>
                        <tr>
                            <td><strong>Regular</strong></td>
                            <td class="text-center">{{ isset($regular['qty']) ? $regular['qty'] : 0 }}</td>
                            <td class="text-center">{{ isset($regular['percentage']) ? $regular['percentage'] : 0 }} %</td>
                        </tr>
                        <tr>
                            <td><strong>Personal</strong></td>
                            <td class="text-center">{{ isset($personal['qty']) ? $personal['qty'] : 0 }}</td>
                            <td class="text-center">{{ isset($personal['percentage']) ? $personal['percentage'] : 0}} %</td>
                        </tr>

                        <tr>
                            <td colspan="6" style="padding:5px"></td>
                        </tr>
                        <tr class="bg-primary">
                            <td><strong>TOTAL</strong></td>
                            <td class="text-center"><strong>{{ $sum_pizza_size }}</strong></td>
                            <td class="text-center">
                                <strong>
                                    {{(isset($jumbo['percentage']) ? $jumbo['percentage'] : 0)
                                    + (isset($regular['percentage']) ? $regular['percentage'] : 0)
                                    + (isset($personal['percentage']) ? $personal['percentage'] : 0)}} %
                                </strong>
                            </td>
                        </tr>
                        </tbody>
                    </table>


                    <?php echo view("report.footer"); ?>
                </div>
            </div>
        </div>
    </section>

    <style>@media print{@page {size: portrait }}</style>
@endsection