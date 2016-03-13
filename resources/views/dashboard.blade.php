@extends('dms::template.default')

@section('content')
    <div class="row dms-dashboard">
        @if ($analyticsWidgets)
        <div class="col-xs-12 col-sm-6">
            <div class="panel panel-default">
                <div class="panel-heading">Analytics</div>
                <div class="panel-body">
                    {!! $analyticsWidgets !!}
                </div>
            </div>
        </div>
        @endif
        <div class="col-xs-12 @if($analyticsWidgets) col-sm-6 @endif">
            <div class="row">
                @foreach($navigation as $element)
                    @if($element instanceof \Dms\Web\Laravel\View\NavigationElementGroup)
                        <div class="col-sm-12">
                            <div class="box box-default">
                                <div class="box-header with-border">
                                    <h3 class="box-title">
                                        <i class="fa fa-{{ $element->getIcon() }}"></i>
                                        {{ $element->getLabel() }}
                                    </h3>
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body">
                                    <div class="list-group">
                                        @foreach($element->getElements() as $innerElement)
                                            <a class="list-group-item" href="{{ $innerElement->getUrl() }}">
                                                <i class="fa fa-{{ $innerElement->getIcon() }}"></i>
                                                {{ $innerElement->getLabel() }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                                <!-- /.box-body -->
                            </div>
                            <!-- /.box -->
                        </div>
                    @elseif ($element instanceof \Dms\Web\Laravel\View\NavigationElement && $element->getUrl() !== route('dms::index'))
                        <div class="col-sm-12">
                            <div class="box box-default">
                                <div class="box-header with-border">
                                    <h3 class="box-title">
                                        <i class="fa fa-{{ $element->getIcon() }}"></i>
                                        {{ $element->getLabel() }}
                                    </h3>
                                </div>
                                <!-- /.box-header -->
                                <div class="box-body">
                                    <div class="list-group">
                                        <a href="{{ $element->getUrl() }}" class="list-group-item">
                                            <i class="fa fa-{{ $element->getIcon() }}"></i>
                                            {{ $element->getLabel() }}
                                        </a>
                                    </div>
                                    <!-- /.box-body -->
                                </div>
                                <!-- /.box -->
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endsection