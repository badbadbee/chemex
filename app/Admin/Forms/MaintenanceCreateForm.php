<?php

namespace App\Admin\Forms;

use App\Models\DeviceRecord;
use App\Models\MaintenanceRecord;
use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Http\JsonResponse;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class MaintenanceCreateForm extends Form implements LazyRenderable
{
    use LazyWidget;

    /**
     * 处理表单提交逻辑
     * @param array $input
     * @return JsonResponse
     */
    public function handle(array $input): JsonResponse
    {
        // 获取物品类型
        $item = $this->payload['item'] ?? null;

        // 获取物品id
        $item_id = $this->payload['item_id'] ?? null;

        // 获取故障说明，来自表单传参
        $ng_description = $input['ng_description'] ?? null;

        // 获取故障时间，来自表单传参
        $ng_time = $input['ng_time'] ?? null;

        // 如果没有物品、物品id、故障说明、故障时间则返回错误
        if (!$item || !$item_id || !$ng_description || !$ng_time) {
            return $this->response()
                ->error('参数错误');
        }

        switch ($item) {
            case 'part':
                $class = '\\Celaraze\\Chemex\\Part\\Models\\PartRecord';
                $item_record = $class::where('id', $item_id)->first();
                break;
            default:
                $item_record = DeviceRecord::where('id', $item_id)->first();
        }

        // 如果没有找到这个物品记录则返回错误
        if (!$item_record) {
            return $this->response()
                ->error('物品不存在');
        }

        // 创建新的配件追踪
        $maintenance_record = new MaintenanceRecord();
        $maintenance_record->item = $item;
        $maintenance_record->item_id = $item_id;
        $maintenance_record->ng_description = $ng_description;
        $maintenance_record->ng_time = $ng_time;
        $maintenance_record->status = 0;
        $maintenance_record->save();

        return $this->response()
            ->success('维修记录保存成功')
            ->refresh();
    }

    /**
     * 构造表单
     */
    public function form()
    {
        $this->text('ng_description', '故障说明')->required();
        $this->datetime('ng_time', '故障时间')->required();
    }
}
