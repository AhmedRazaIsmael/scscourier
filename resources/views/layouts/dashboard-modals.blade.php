@php
    // Default actions if not passed from page
    $filterAction = $filterAction ?? url()->current();
    $rowFilterAction = $rowFilterAction ?? url()->current();
    $sortAction = $sortAction ?? url()->current();
    $aggregateAction = $aggregateAction ?? url()->current();
    $computeAction = $computeAction ?? url()->current();
    $chartAction = $chartAction ?? url()->current();
    $downloadAction = $downloadAction ?? url()->current();
    $groupByAction = $groupByAction ?? url()->current();
    $controlBreakAction = $controlBreakAction ?? url()->current();
    $columns = $columns ?? [];
    $chartModel = $chartModel ?? '';
@endphp

<!-- ✅ COLUMN FILTER MODAL -->
<div class="modal fade" id="columnFilterModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="GET" action="{{ $filterAction }}" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Filter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label>Column</label>
                    <select name="filter_column" class="form-select">
                        <option value="">-- Select --</option>
                        @foreach($columns as $col)
                            <option value="{{ $col }}">{{ ucfirst(str_replace('_',' ',$col)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>Operator</label>
                    <select name="filter_operator" class="form-select">
                        <option value="=">=</option>
                        <option value="!=">!=</option>
                        <option value="like">LIKE</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Value</label>
                    <input type="text" name="filter_value" class="form-control" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<!-- ✅ ROW FILTER MODAL -->
<div class="modal fade" id="rowFilterModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="GET" action="{{ $rowFilterAction }}" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Row Filter</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>Filter Expression</label>
                <textarea name="row_filter_expression" rows="3" class="form-control" placeholder="e.g. country == 'Pakistan' && state == 'Punjab'"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<!-- ✅ SORT MODAL -->
<div class="modal fade" id="sortModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="GET" action="{{ $sortAction }}" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Sort</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @for($i=0;$i<3;$i++)
                <div class="row mb-2">
                    <div class="col-1">{{ $i+1 }}</div>
                    <div class="col-6">
                        <select name="sort_columns[{{ $i }}][column]" class="form-select">
                            <option value="">-- Select Column --</option>
                            @foreach($columns as $col)
                                <option value="{{ $col }}">{{ ucfirst($col) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-5">
                        <select name="sort_columns[{{ $i }}][direction]" class="form-select">
                            <option value="asc">ASC</option>
                            <option value="desc">DESC</option>
                        </select>
                    </div>
                </div>
                @endfor
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<!-- ✅ AGGREGATE MODAL -->
<div class="modal fade" id="aggregateModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="GET" action="{{ $aggregateAction }}" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Aggregate</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>Function</label>
                <select name="aggregate_function" class="form-select mb-3" required>
                    <option value="count">Count</option>
                    <option value="sum">Sum</option>
                    <option value="avg">Average</option>
                    <option value="min">Min</option>
                    <option value="max">Max</option>
                </select>
                <label>Column</label>
                <select name="aggregate_column" class="form-select" required>
                    @foreach($columns as $col)
                        <option value="{{ $col }}">{{ ucfirst($col) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<!-- ✅ COMPUTE MODAL -->
<div class="modal fade" id="computeModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ $computeAction }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Compute</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>Expression</label>
                <textarea class="form-control mb-3" name="compute_expression" placeholder="e.g., name . ' - ' . code"></textarea>
                <div class="d-flex flex-wrap gap-2">
                    @foreach(['(', ')', '.', "'", '+', '-', '*', '/', ','] as $symbol)
                        <button type="button" class="btn btn-sm btn-outline-dark" onclick="insertToExpression('{{ $symbol }}')">{{ $symbol }}</button>
                    @endforeach
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<!-- ✅ CHART MODAL -->
<div class="modal fade" id="chartModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="GET" action="{{ $chartAction }}" class="modal-content">
            <input type="hidden" name="model" value="{{ $chartModel }}">
            <div class="modal-header">
                <h5 class="modal-title">Chart</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>X-axis</label>
                <select name="label" class="form-select mb-3">
                    @foreach($columns as $col)
                        <option value="{{ $col }}">{{ ucfirst($col) }}</option>
                    @endforeach
                </select>
                <label>Y-axis</label>
                <select name="value" class="form-select mb-3">
                    @foreach($columns as $col)
                        <option value="{{ $col }}">{{ ucfirst($col) }}</option>
                    @endforeach
                </select>
                <label>Function</label>
                <select name="function" class="form-select mb-3">
                    <option value="count">Count</option>
                    <option value="sum">Sum</option>
                    <option value="avg">Average</option>
                </select>
                <label>Chart Type</label>
                <select name="type" class="form-select">
                    <option value="bar">Bar</option>
                    <option value="line">Line</option>
                    <option value="pie">Pie</option>
                </select>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Apply</button>
            </div>
        </form>
    </div>
</div>

<!-- ✅ DOWNLOAD MODAL -->
<div class="modal fade" id="downloadModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="GET" action="{{ $downloadAction }}" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Download</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label>Choose format</label>
                <div class="d-flex gap-3 mb-3">
                    @foreach(['csv'=>'CSV','xlsx'=>'Excel','html'=>'HTML'] as $ext=>$label)
                        <div class="form-check">
                            <input type="radio" class="form-check-input" name="format" value="{{ $ext }}" {{ $loop->first ? 'checked' : '' }}>
                            <label class="form-check-label">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="data_only">
                    <label class="form-check-label">Data Only</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Download</button>
            </div>
        </form>
    </div>
</div>
