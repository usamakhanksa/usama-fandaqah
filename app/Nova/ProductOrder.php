<?php
namespace App\Nova;
use Laravel\Nova\Resource;
use Laravel\Nova\Fields\ID;
class ProductOrder extends Resource {
  public static string $model = \App\Models${r}::class;
  public static $title = 'id';
  public static $search = ['id'];
  public function fields(\Laravel\Nova\Http\Requests\NovaRequest $request): array { return [ID::make()->sortable()]; }
}
