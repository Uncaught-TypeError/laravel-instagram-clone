<?php

namespace App\DataTables;

use App\Models\Post;
use Illuminate\Support\Str;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PostDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('user', function (Post $post) {
                return '<a href="' . route('users.show', $post->user) .'">'. $post->user->name .'</a>';
            })
            ->addColumn('action', function (Post $post) {
                $btn = '<a href="javascript:void(0)" class="viewBtn btn btn-info btn-sm">View</a>';
                $btn .= '<a href="javascript:void(0)" class="editBtn btn btn-primary btn-sm">Edit</a>';
                $btn .= '<a href="javascript:void(0)" class="deleteBtn btn btn-danger btn-sm">Delete</a>';
                return '<span data-slug = "' . $post->slug . '">' . $btn .'</span>';
            })
            ->addColumn('thumb', function (Post $post) {
                return '<img src="' . $post->getFirstMediaUrl('posts', 'thumb') .'" height="50px" />';
            })
            ->rawColumns(['user', 'action', 'thumb'])
            ->editColumn('caption', function (Post $post) {
                return Str::limit($post->caption, 15);
            })
            ->editColumn('created_at', function (Post $post) {
                return $post->created_at->format('g:i A, d M Y');
            })
            ->editColumn('updated_at', function (Post $post) {
                return $post->updated_at->format('g:i A, d M Y');
            });
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\Models\Post $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Post $model)
    {
        return $model->newQuery()->with(['user', 'media']);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('post-table')
                    ->columns($this->getColumns())
                    ->minifiedAjax()
                    ->dom('Bfrtip')
                    ->orderBy(1)
                    ->buttons(
                        Button::make('create'),
                        Button::make('export'),
                        Button::make('print'),
                        Button::make('reset'),
                        Button::make('reload')
                    )
                    ->scrollX(true);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            Column::make('id'),
            Column::computed('user')
                ->exportable(true)
                ->printable(true),
            Column::make('caption'),
            Column::computed('thumb'),
            Column::make('created_at'),
            Column::make('updated_at'),
            Column::computed('action')
                ->exportable(false)
                ->printable(false)
                ->addClass('text-center'),
        ];
    }
}
