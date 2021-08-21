<?php

namespace App\Http\Controllers\Litepie;

use App\Forms\Master as MasterForm;
use App\Http\Requests\Litepie\MasterRequest;
use App\Repositories\Eloquent\Filters\MasterResourceFilter;
use App\Repositories\Eloquent\Presenters\MasterListPresenter;
use Exception;
use Litepie\Http\Controllers\ResourceController as BaseController;
use Litepie\Master\Interfaces\MasterRepositoryInterface;
use Litepie\Repository\Filter\RequestFilter;

/**
 * Resource controller class for master.
 */
class MasterResourceController extends BaseController
{

    /**
     * Initialize master resource controller.
     *
     *
     * @return null
     */
    public function __construct(MasterRepositoryInterface $master)
    {
        parent::__construct();
        $this->form = MasterForm::setAttributes()->toArray();
        $this->modules = $this->modules(config('master.modules'), 'master', guard_url('master'));
        $this->repository = $master;
    }

    /**
     * Display a list of master.
     *
     * @return Response
     */
    public function index(MasterRequest $request, $group = 'masters', $type = null)
    {

        $pageLimit = $request->input('pageLimit', config('database.pagination.limit'));
        $data = $this->repository
            ->pushFilter(RequestFilter::class)
            ->pushFilter(MasterResourceFilter::class)
            ->setPresenter(MasterListPresenter::class)
            ->simplePaginate($pageLimit)
        // ->withQueryString()
            ->toArray();

        extract($data);
        $form = $this->form;
        $modules = $this->modules;
        $count = $this->repository->typeCount();
        $groups = $this->repository->groups();

        return $this->response->setMetaTitle(trans('master::master.names'))
            ->view('litepie.master.index')
            ->data(compact('data', 'meta', 'links', 'groups', 'type', 'modules', 'form', 'count', 'groups'))
            ->output();
    }

    /**
     * Display master.
     *
     * @param Request $request
     * @param Model   $master
     *
     * @return Response
     */
    public function show(MasterRequest $request, MasterRepositoryInterface $repository)
    {
        $form = $this->form;
        $modules = $this->modules;
        $data = $repository->toArray();
        return $this->response
            ->setMetaTitle(trans('app.view') . ' ' . trans('master::master.name'))
            ->data(compact('data', 'form', 'modules', 'form'))
            ->view('litepie.master.show')
            ->output();
    }

    /**
     * Show the form for creating a new master.
     *
     * @param Request $request
     *x
     * @return Response
     */
    public function create(MasterRequest $request, MasterRepositoryInterface $repository)
    {
        $form = $this->form;
        $modules = $this->modules;
        $data = $repository->toArray();
        return $this->response->setMetaTitle(trans('app.new') . ' ' . trans('master::master.name'))
            ->view('litepie.master.create')
            ->data(compact('data', 'form', 'modules'))
            ->output();
    }

    /**
     * Create new master.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function store(MasterRequest $request, MasterRepositoryInterface $repository)
    {
        try {
            $attributes = $request->all();
            $attributes['user_id'] = user_id();
            $attributes['user_type'] = user_type();
            $repository->create($attributes);
            $data = $repository->toArray();

            return $this->response->message(trans('messages.success.created', ['Module' => trans('master::master.name')]))
                ->code(204)
                ->data(compact('data'))
                ->status('success')
                ->url(guard_url('master/master/' . $data['id']))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('/master/master'))
                ->redirect();
        }

    }

    /**
     * Show master for editing.
     *
     * @param Request $request
     * @param Model   $master
     *
     * @return Response
     */
    public function edit(MasterRequest $request, MasterRepositoryInterface $repository)
    {
        $form = $this->form;
        $modules = $this->modules;
        $data = $repository->toArray();

        return $this->response->setMetaTitle(trans('app.edit') . ' ' . trans('master::master.name'))
            ->view('litepie.master.edit')
            ->data(compact('data', 'form', 'modules'))
            ->output();
    }

    /**
     * Update the master.
     *
     * @param Request $request
     * @param Model   $master
     *
     * @return Response
     */
    public function update(MasterRequest $request, MasterRepositoryInterface $repository)
    {
        try {
            $attributes = $request->all();
            $repository->update($attributes);
            $data = $repository->toArray();

            return $this->response->message(trans('messages.success.updated', ['Module' => trans('master::master.name')]))
                ->code(204)
                ->status('success')
                ->data(compact('data'))
                ->url(guard_url('master/master/' . $data['id']))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('master/master/' . $data['id']))
                ->redirect();
        }

    }

    /**
     * Remove the master.
     *
     * @param Model   $master
     *
     * @return Response
     */
    public function destroy(MasterRequest $request, MasterRepositoryInterface $repository)
    {
        try {
            $repository->delete();
            $data = $repository->toArray();

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('master::master.name')]))
                ->code(202)
                ->status('success')
                ->data(compact('data'))
                ->url(guard_url('master/master/0'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('master/master/' . $data['id']))
                ->redirect();
        }

    }
}
