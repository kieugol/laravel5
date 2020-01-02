<section class="content">
    <table class="table pad5 no-border">
        <tbody>
            <tr style="border-top: 1px solid black; border-bottom: 1px solid black">
                <th class="text-left w80 bold">Name</th>
                <th class="text-left bold">Type</th>
                <th class="text-left bold">Bank</th>
                <th class="text-left bold">Discount Percent</th>
            </tr>
            <tr><td class="" colspan="15"></td></tr>
        @foreach($detail as $row)
            <tr>
                <td class="text-left">{{ $row->name }}</td>
                <td class="text-left">{{ $row->type}}</td>
                <td class="text-left">{{ $row->bank}}</td>
                <td class="text-left">{{ $row->discount_percent}}</td>
             </tr>
        @endforeach
        </tbody>
    </table>
</section>
