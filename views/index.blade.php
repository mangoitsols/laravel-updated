<!DOCTYPE html>
<html lang="en">

<head>
    @include('frontend.partials.head')
    <script type="text/javascript" src="{{asset('admin/assets/js/pages/editor_ckeditor.js')}}"></script>
</head>

<style>
    .import-label{
        position: absolute; 
        background-color: #32c132; 
        color: white; 
        width: 100%; 
        padding: 3px; 
        text-align: center;
        border: 3px solid white;
    }
</style>

<body>
    @include('frontend.partials.header')
    <!-- Page container -->
    <div class="page-container">
        <!-- Page content -->
        <div class="page-content">
            @include('frontend.partials.menu')
            <!-- Main content -->
            <div class="content-wrapper">
                <div class="content">
                    <!-- Page header -->
                    <div class="page-header page-header-default">
                        <div class="page-header-content">
                            <div class="page-title">
                                <h4><i class="icon-arrow-left52 position-left"></i> <span
                                        class="text-semibold">Import IDX Listing</span></h4>
                            </div>
                        </div>
                        <div class="breadcrumb-line">
                            <ul class="breadcrumb">
                                <li><a href="index.html"><i class="icon-home2 position-left"></i> Home</a></li>
                                <li><a href="form_inputs_basic.html">Import Listing</a></li>
                            </ul>
                        </div>
                    </div>

                    <div class="panel panel-flat">
                        
                        <div class="panel-body">

                            @if(isset($properties) && !empty($properties))

                                <form method="post" action="{{ route('import.listing') }}">
                                    @csrf
                                    
                                    <div>
                                        <input type="submit" class="btn btn-lg pull-right" name="importBtn" value="Import Listing" style="background-color: green; color: white;">
                                       <input type="checkbox" class="form-check-input" id="select_all">
                                       <label class="form-check-label" for="select_all">Select/Deselect All</label>
                                       <p>Importing listings may take some time. Please be patient.</p>

                                        @if(session('success'))
                                            <div style="display: flex; justify-content: center;">
                                                <div class="alert alert-success text-center" style="min-width: 300px;">
                                                    <strong>{{session('success')}}</strong>
                                                </div>
                                            </div>
                                        @endif
                                    </div>


                                    <div style="display: flex; flex-wrap: wrap; justify-content: center;">

                                        @foreach ($properties as $prop)

                                            <div style="width: 290px; border: 2px solid #8d8f90; margin: 15px 10px 15px 10px; position: relative;">

                                                @if(in_array($prop['listingID'], $listing_ids))
                                                    <div class="import-label">
                                                        <span class="glyphicon glyphicon-ok"></span>&nbsp IMPORTED</span>
                                                    </div>
                                                @endif

                                                <label for="{{ $prop['listingID'] }}" class="idx-listing" style="padding: 4px; cursor: pointer;">

                                                    <img class="listing lazy img-responsive" src="{{ isset($prop['image']['0']['url']) ? $prop['image']['0']['url'] : '//mlsphotos.idxbroker.com/defaultNoPhoto/noPhotoFull.png' }}">

                                                    <div class="impress-import-info-container" style="padding: 2px">
                                                        <span class="price">{{ $prop['listingPrice'] }}</span><br/>
                                                        <span class="address">{{ isset($prop['address']) ? $prop['address'] : 'Address unlisted' }}</span><br/>
                                                        <span class="mls">MLS#: </span>{{ $prop['listingID'] }}
                                                    </div>

                                                    @if(!in_array($prop['listingID'], $listing_ids))
                                                        <input type="checkbox" id="{{ $prop['listingID'] }}" class="checkbox pull-right" name="listings_idx[]" value="{{ $prop['listingID'] }}"/>
                                                    @endif

                                                </label>

                                            </div>

                                        @endforeach

                                    </div>

                                    <div class="col-lg-12">
                                        <input type="submit" class="btn btn-lg pull-right" name="importBtn" value="Import Listings" style="background-color: green; color: white;">
                                    </div>

                                </form>
                            @else
                                <h5>No Listing found. / Please validate your API key.</h5>
                            @endif

                        </div>
                    </div>

                </div>
            </div>
            <!-- /page content -->
        </div>
        <!-- /page container -->
</body>


@include('frontend.partials.footer')

<script>

    $('#select_all').change(function() {

        if($(this).is(':checked')){
            $('#select_all').text('Select All');
        }else{
            $('#select_all').text('Deselect All');
        }

        var checkboxes = $(this).closest('form').find(':checkbox');
        checkboxes.prop('checked', $(this).is(':checked'));
    });


    setTimeout(function(){
        $('.alert-success').remove();
    },4000)

</script>

</html>
