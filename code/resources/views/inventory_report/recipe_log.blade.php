@extends($layout)

@section('content')
    @include('report.module_filter')
    <section class="content">
        <div id="box-report" class="bg-white p20">
            @include('report.header')
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="text-left">Recipe</th>
                    <th class="text-left">Material From</th>
                    <th class="text-left">Other Recipe From</th>
                    <th class="text-left">Material To</th>
                    <th class="text-left">Other Recipe To</th>
                    <th class="text-left">Usage From</th>
                    <th class="text-left">Usage To</th>
                    <th class="text-left">Updated Date</th>
                    <th class="text-left">Updated By</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $item): ?>
                <tr>
                    <td class="text-left">{{ $item->recipe->name }}</td>
                    <td class="text-left">{{ !empty($item->material_from_id) ? $item->material_from->name : '' }}</td>
                    <td class="text-left">{{ !empty($item->other_recipe_from_id) ? $item->other_recipe_from->name : '' }}</td>
                    <td class="text-left">{{ !empty($item->material_to_id) ? $item->material_to->name : '' }}</td>
                    <td class="text-left">{{ !empty($item->other_recipe_to_id) ? $item->other_recipe_to->name : '' }}</td>
                    <td class="text-left">{{ $item->usage_from }}</td>
                    <td class="text-left">{{ $item->usage_to }}</td>
                    <td class="text-left">{{ $item->created_date }}</td>
                    <td class="text-left">{{ !empty($item->user_created) ? $item->user_created->name : '' }}</td>
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
    </style>


@endsection
