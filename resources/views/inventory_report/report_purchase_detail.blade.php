@extends($layout)

@section('content')
    @include('report.module_filter')
    <section class="content">
        <div id="box-report" class="bg-white p20">
            <div class="text-center">
                <h3>PURCHASE ORDER</h3>
                <h2>PT.SARIMELATI KENCANA TBK</h2>
                <h4>PIZZA HUT SUPPORT CENTER</h4>
                <p>
                    <span>Graha Mustika Ratu Lantai 8</span>
                    <br>
                    <span>Jl.Gatot Subroto Kav. 74075, Jakata 12870</span>
                    <br>
                    <span>Telp : (021) 830 6789 (Hunting)</span>
                    <br>
                    <span>Fax : (021) 830 6790</span>
                    <br>
                </p>
            </div>
            <table>
                <tr>
                    <td></td>
                    <td class="text-right"><b>No. </b>{{ $data->code }}</td>
                </tr>
                <tr>
                    <td class="col-sm-5">
                        <table class="border w100percent">
                            <tr>
                                <td>
                                    <span class="border-bottom">To :</span>
                                    <br>
                                    <span>Kepada :</span>
                                </td>
                                <td>{{ $data->supplier->name }}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Telp.{{ $data->supplier->phone }}</td>
                            </tr>
                            <tr>
                                <td></td>
                                <td>Fax. {{ $data->supplier->fax }}</td>
                            </tr>
                        </table>
                        <p>
                            <span class="border-bottom">Please deliver the following order to</span>
                            <br>
                            <span>Harap kirim order berikut ini kepada</span>
                        </p>
                    </td>
                    <td class="col-sm-7">
                        <table>
                            <tr>
                                <td class="col-sm-2">Outlet</td>
                                <td class="col-sm-1">:</td>
                                <td class="col-sm-9">{{ $outlet['outlet_code'] . '. ' . $outlet['outlet_name'] }}</td>
                            </tr>
                            <tr>
                                <td class="col-sm-2">Date / Tgl </td>
                                <td class="col-sm-1">:</td>
                                <td class="col-sm-9">{{ $data->delivery_date }}</td>
                            </tr>
                            <tr>
                                <td class="col-sm-2">Address</td>
                                <td class="col-sm-1">:</td>
                                <td class="col-sm-9">{{ $outlet['outlet_address'] }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <table class="table">
                <thead>
                <tr class="border">
                    <th class="text-left border-bottom">Quantity</th>
                    <th class="text-left border-bottom">Unit</th>
                    <th class="text-left border-bottom">Description / Uraian Barang</th>
                    <th class="text-left border-bottom">Unit price / Harga Satuan</th>
                    <th class="text-left border-bottom">Amount / Jumlah</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data->purchase_detail as $item): ?>
                <tr>
                    <td class="text-right border-left">{{ $item->quantity }}</td>
                    <td class="text-left">{{ $item->uom }}</td>
                    <td class="text-left">{{ $item->material_detail_name }}</td>
                    <td class="text-right">{{ number_format($item->price) }}</td>
                    <td class="text-right border-right">{{ number_format($item->total) }}</td>
                </tr>
                <?php endforeach; ?>
                <tr>
                    <td class="text-right border-left border-bottom border-top"></td>
                    <td class="text-left border-bottom border-top"></td>
                    <td colspan="2" class="text-center border-bottom border-top">
                        <table>
                            <tr>
                                <td>Delivery</td>
                                <td>:</td>
                                <td>{{ $data->delivery_date }}</td>
                            </tr>
                            <tr>
                                <td>Term of Payment </td>
                                <td>:</td>
                                <td>0 day(s)</td>
                            </tr>
                            <tr>
                                <td>PPN</td>
                                <td>:</td>
                                <td></td>
                            </tr>
                            <tr>
                                <td>Charge to A/C No</td>
                                <td>:</td>
                                <td></td>
                            </tr>
                        </table>
                    </td>
                    <td class="text-right bg-gray border border-top"></td>
                </tr>
                <tr>
                    <td colspan="3" class="text-left no-border vertical-align-bottom">
                        <p>1. Invoice intriplicate in the name of <b>PT.SARIMELATI KENCANA TBK.</b> NPWP : 01323.964.5-092.000<br>
                            & Packing list must eccompany the delivery of goods ordered.</p>
                    </td>
                    <td class="text-left center-block text-center">
                        <span>Total</span>
                        <br>
                        <br>
                        <span class=""><b>Rp.</b></span>
                        <br>
                        <span class="">( ..................... )</span>
                    </td>
                    <td class="text-right border-left border-right border-bottom">{{ $data->total }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-left no-border">
                        <p>
                            Invoice dalam rangkap 3, atas nama <b>PT.SARIMELATI KENCANA TBK.</b> berikut packing list harus disertakan pada waku menyerahkan barang yang dipesan.
                        </p>
                        <p>
                            2. Delivery is accepted subject to check on quantiy and quanlity.<br>
                            Penyerahan barang dianggap diterima setelah dicek jumlahnya dan kualitasnya.
                        </p>
                        <p>
                            3. Stamp duty (materal) should be charged to supplier.<br>
                            Biaya materal menjadi tanggungan supplier.
                        </p>
                    </td>
                    <td colspan="2" class="vertical-align-middle text-center">
                        <span class="border-bottom"> {{ $user->name }}</span>
                        <br>
                        <span>Restorant / Outlet Manager</span>
                    </td>
                </tr>
                </tbody>
            </table>

            <?php echo view("report.footer"); ?>
        </div>
        <br/>
        <div class="text-center">
            <a class="btn btn-sm btn-default form-history-back"><i class="fa fa-arrow-left"></i>&nbsp;Back</a>
        </div>
    </section>
    <style>
        @media print{@page {size: landscape}}
    </style>
@endsection
