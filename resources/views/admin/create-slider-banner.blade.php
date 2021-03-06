@extends('layouts.admin')

@section('dashboard')

    <!-- sidebar -->
    @include('admin.partials._sidebar')
    <!-- sidebar -->

    <!-- top navigation -->
    @include('admin.partials._navigation')
    <!-- /top navigation -->

    <!-- page content -->
    <div class="right_col" role="main">
        <div class="">
            <div class="clearfix"></div>

            <div class="row">
                <div class="col-md-12 col-sm-12 col-xs-12">
                    <div class="x_panel">
                        <div class="x_title">
                            @if($type == 'top_first_banner')
                                <h2>Create New Top First Banner</h2>
                            @else
                                <h2>Create New Top Second Banner</h2>
                            @endif
                            <ul class="nav navbar-right panel_toolbox">
                                <li><a class="collapse-link"><i class="fa fa-chevron-up"></i></a>
                                </li>
                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-wrench"></i></a>
                                    <ul class="dropdown-menu" role="menu">
                                        <li><a href="#">Settings 1</a>
                                        </li>
                                        <li><a href="#">Settings 2</a>
                                        </li>
                                    </ul>
                                </li>
                                <li><a class="close-link"><i class="fa fa-close"></i></a>
                                </li>
                            </ul>
                            <div class="clearfix"></div>
                        </div>
                        <div class="x_content">

                            {!! Form::open(array('action' => array('Admin\BannerController@store', $type), 'method' => 'POST', 'role' => 'form', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal form-label-left', 'novalidate')) !!}
                            {{ csrf_field() }}

                            @if($errors->count() > 0)
                                <div class="form-group">
                                    <div class="col-md-3 col-sm-3 col-xs-12"></div>
                                    <div class="col-md-6 col-sm-6 col-xs-12">
                                        <div class="alert alert-danger" role="alert">
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span>
                                            </button>
                                            @foreach($errors->all() as $error)
                                                {{ $error }}<br/>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Slider Image <span class="required">*</span>
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">

                                    {!! Form::file('image', array('id' => 'image', 'class' => 'file-loading')) !!}
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12" for="name">Caption (Optional)
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input id="caption" name="caption"  class="form-control col-md-7 col-xs-12" type="text">
                                </div>
                            </div>
                            <div class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Link to Product or Gallery
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <div class="btn-group" data-toggle="buttons">
                                        <label class="btn btn-default active">
                                            <input type="radio" name="options" value="no" id="link-no-opt" checked> No
                                        </label>
                                        <label class="btn btn-default">
                                            <input type="radio" name="options" value="link-product" id="link-product-opt"> Product
                                        </label>
                                        <label class="btn btn-default">
                                            <input type="radio" name="options" value="link-gallery" id="link-gallery-opt"> Gallery
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div id="product-select" class="item form-group" style="display: none;">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Choose Product
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select id="product" name="product" class="form-control" style="width: 100%">
                                        <option value="-1">Select a product</option>

                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                            <div id="gallery-select" class="item form-group" style="display: none;">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">Choose Gallery
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <select id="gallery" name="gallery" class="form-control" style="width: 100%">
                                        <option value="-1">Select a product</option>

                                        @foreach($galleries as $gallery)
                                            <option value="{{ $gallery->id }}">{{ $gallery->name }}</option>
                                        @endforeach

                                    </select>
                                </div>
                            </div>
                            <div id="banner-url-input" class="item form-group">
                                <label class="control-label col-md-3 col-sm-3 col-xs-12">URL
                                </label>
                                <div class="col-md-6 col-sm-6 col-xs-12">
                                    <input id="url" name="url" class="form-control col-md-7 col-xs-12" required>
                                </div>
                            </div>
                            <div class="ln_solid"></div>
                            <div class="form-group">
                                <div class="col-md-6 col-md-offset-3">
                                    <a href="{{ route('slider-banner-list', ['type' => $type]) }}" class="btn btn-primary">Cancel</a>
                                    <button id="send" type="submit" class="btn btn-success">Create</button>
                                </div>
                            </div>

                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- page content -->

    <!-- footer content -->
    @include('admin.partials._footer')
    <!-- footer content -->

@endsection