<?php /** @var \Dms\Web\Laravel\Renderer\Action\ActionButton[] $actionButtons */ ?>

@foreach($actionButtons as $actionButton)
    @if($actionButton->isPost())
        <form class="dms-run-action-form inline" action="{{ $actionButton->getUrl($objectId) }}" method="post">
            {!! csrf_field() !!}
            <button type="submit"
                    class="btn btn-{{ \Dms\Web\Laravel\Util\KeywordTypeIdentifier::getClass($actionButton->getName()) ?? 'default' }}">
                {{ $actionButton->getLabel() }}
            </button>
        </form>
    @else
        <a class="btn btn-{{ \Dms\Web\Laravel\Util\KeywordTypeIdentifier::getClass($actionButton->getName()) ?? 'default' }}"
           href="{{ $actionButton->getUrl($objectId) }}">
            {{ $actionButton->getLabel() }}
        </a>
    @endif
@endforeach