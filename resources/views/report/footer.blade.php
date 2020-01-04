<table class="table no-border hide">
    <tr>
        <td style="width:150px;">PRINTED by</td>
        <td><?php echo Auth::guard("admin")->user()->name; ?></td>
    </tr>
    <tr>
        <td><?php echo date("Y-m-d"); ?></td>
        <td><?php echo date("H:i:s"); ?></td>
    </tr>
</table>

<style>
    @media print{
        @page {
            size: auto; 
            margin: 8mm; 
        } 
/*        @page {
            @bottom-right {
                content: counter(page) " of " counter(pages);
            }
        }
        @page {
            @bottom-left {
                content: counter(page) " of " counter(pages);
            }
        }*/
        thead {display: table-header-group}
        #print-space > div {padding: 0px !important}
    }
</style>