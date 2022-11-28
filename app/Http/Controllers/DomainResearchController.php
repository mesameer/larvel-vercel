<?php

namespace App\Http\Controllers;

use App\Models\Industries;
use App\Models\Location;
use App\Models\LocationGroup;
use App\Models\LocationGroupCustomZipCode;
use App\Models\LocationGroupRecord;
use App\Models\SearchHistory;
use CreateLocationGroupCustomZipCodes;
use Exception;
use Helpers;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Throwable;

class DomainResearchController extends Controller
{
    public function domainReport(Request $request)
    {
        $condition = [];
        $whereIn = [];

        if ($request->get('search_filters')) {
            $search_filters = $request->get('search_filters');

            try {
                $us_result = json_decode($search_filters, true);
            }
            catch (Throwable $e){
                $us_result = false;
            }

            if ($us_result !== false) {
                $search_filters = $us_result;
            }

            $counter = 0;
            foreach($search_filters as $details) {
                if ($details['search_by']) {
                    $search_by = $details['search_by'];
                    
                    if ($search_by == 'Group Name') {
                        $locationIds = LocationGroupRecord::select('location_group_records.location_id')
                                        ->join('location_groups', 'location_groups.id', '=', 'location_group_records.location_group_id')
                                        ->where('group_name', $details['search']);

                        $whereIn[$counter]['id'] = $locationIds;
                    }
                    else if ($search_by == 'Country Name') {
                        $condition[$counter]['country'] = $details['search'];
                    }
                    else if ($search_by == 'State Name') {
                        $condition[$counter]['state_name'] = $details['search'];
                        $condition[$counter]['country'] = $details['search_country'];
                    }
                    else if ($search_by == 'County Name') {
                        $condition[$counter]['county_name'] = $details['search'];
                        $condition[$counter]['state_name'] = $details['search_state'];
                        $condition[$counter]['country'] = $details['search_country'];
                    }
                    else if ($search_by == 'City Name') {
                        $condition[$counter]['city_name'] = $details['search'];
                        $condition[$counter]['county_name'] = $details['search_county'];
                        $condition[$counter]['state_name'] = $details['search_state'];
                        $condition[$counter]['country'] = $details['search_country'];
                    }
                }

                $counter++;
            }
        }

        $record_type = $request->get('record_type');
        $type = $request->get('type');

        $uniqueCount = [];
        
        $uniqueCount['country'] = Location::where(function ($q) use ($condition) {
            foreach ($condition as $single_condition) {
                $q->orWhere(function ($q) use ($single_condition) {
                    foreach ($single_condition as $key => $value) {
                        $q->where($key, '=', $value);
                    }
                });
            }
        })->distinct('country')->count();
        
        $queryCondition = '';

        if (!empty($condition)) {
            $queryCondition = 'WHERE';

            foreach ($condition as $single_condition) {
                $queryCondition .= ' (';
                foreach ($single_condition as $key => $value) {
                    $queryCondition .= $key . ' = \''. $value .'\' AND ';
                }

                $queryCondition = rtrim($queryCondition, ' AND ');

                $queryCondition .= ') OR ';
            }

            $queryCondition = rtrim($queryCondition, ' OR ');
        }
        
        $data = DB::select('SELECT COUNT(*) AS totals FROM (SELECT 1 FROM locations '. $queryCondition .' GROUP BY country, state_name) AS temp;');
        $uniqueCount['state_name'] = $data[0]->totals;

        $data = DB::select('SELECT COUNT(*) AS totals FROM (SELECT 1 FROM locations '. $queryCondition .' GROUP BY country, state_name, county_name) AS temp;');
        $uniqueCount['county_name'] = $data[0]->totals;

        $data = DB::select('SELECT COUNT(*) AS totals FROM (SELECT 1 FROM locations '. $queryCondition .' GROUP BY country, state_name, county_name, city_name) AS temp;');
        $uniqueCount['city_name'] = $data[0]->totals;

        $uniqueCount['zip_code'] = Location::where(function ($q) use ($condition) {
            foreach ($condition as $single_condition) {
                $q->orWhere(function ($q) use ($single_condition) {
                    foreach ($single_condition as $key => $value) {
                        $q->where($key, '=', $value);
                    }
                });
            }
        })->where(function ($q) use ($whereIn) {
            foreach ($whereIn as $single_condition) {
                $q->orWhere(function ($q) use ($single_condition) {
                    foreach ($single_condition as $key => $value) {
                        $q->whereIn($key, $value);
                    }
                });
            }
        })->distinct('zip_code')->count();
        
        $uniqueCount['zip_population'] = Location::where(function ($q) use ($condition) {
            foreach ($condition as $single_condition) {
                $q->orWhere(function ($q) use ($single_condition) {
                    foreach ($single_condition as $key => $value) {
                        $q->where($key, '=', $value);
                    }
                });
            }
        })->where(function ($q) use ($whereIn) {
            foreach ($whereIn as $single_condition) {
                $q->orWhere(function ($q) use ($single_condition) {
                    foreach ($single_condition as $key => $value) {
                        $q->whereIn($key, $value);
                    }
                });
            }
        })->sum('zip_population');

        if ($type == 'unique') {
            $group_by = [];
            $select_list = [];

            if ($record_type == 'country') {
                $group_by[] = 'country';

                $select_list[] = 'country';
                $select_list[] = DB::raw('COUNT(DISTINCT state_name) as state_name');
                $select_list[] = DB::raw('COUNT(DISTINCT county_name) as county_name');
                $select_list[] = DB::raw('COUNT(DISTINCT city_name) as city_name');
                $select_list[] = DB::raw('COUNT(zip_code) as zip_code');
                $select_list[] = DB::raw('COUNT(area_code) as area_code');
                $select_list[] = DB::raw('SUM(zip_population) as zip_population');
            }
            else if ($record_type == 'state') {
                $group_by[] = 'country';
                $group_by[] = 'state_name';
                
                $select_list[] = DB::raw('GROUP_CONCAT(DISTINCT country) as country');
                $select_list[] = 'state_name';
                $select_list[] = DB::raw('COUNT(DISTINCT county_name) as county_name');
                $select_list[] = DB::raw('COUNT(DISTINCT city_name) as city_name');
                $select_list[] = DB::raw('COUNT(zip_code) as zip_code');
                $select_list[] = DB::raw('COUNT(area_code) as area_code');
                $select_list[] = DB::raw('SUM(zip_population) as zip_population');
            }
            else if ($record_type == 'county') {
                $group_by[] = 'country';
                $group_by[] = 'state_name';
                $group_by[] = 'county_name';

                $select_list[] = DB::raw('GROUP_CONCAT(DISTINCT country) as country');
                $select_list[] = DB::raw('GROUP_CONCAT(DISTINCT state_name) as state_name');
                $select_list[] = 'county_name';
                $select_list[] = DB::raw('COUNT(DISTINCT city_name) as city_name');
                $select_list[] = DB::raw('COUNT(zip_code) as zip_code');
                $select_list[] = DB::raw('COUNT(area_code) as area_code');
                $select_list[] = DB::raw('SUM(zip_population) as zip_population');
            }
            else if ($record_type == 'city') {
                $group_by[] = 'country';
                $group_by[] = 'state_name';
                $group_by[] = 'county_name';
                $group_by[] = 'city_name';

                $select_list[] = DB::raw('GROUP_CONCAT(DISTINCT country) as country');
                $select_list[] = DB::raw('GROUP_CONCAT(DISTINCT state_name) as state_name');
                $select_list[] = DB::raw('GROUP_CONCAT(DISTINCT county_name) as county_name');
                $select_list[] = 'city_name';
                $select_list[] = DB::raw('COUNT(zip_code) as zip_code');
                $select_list[] = DB::raw('COUNT(area_code) as area_code');
                $select_list[] = DB::raw('SUM(zip_population) as zip_population');
            }

            $locations = Location::select($select_list)->sortable()->where(function ($q) use ($condition) {
                foreach ($condition as $single_condition) {
                    $q->orWhere(function ($q) use ($single_condition) {
                        foreach ($single_condition as $key => $value) {
                            $q->where($key, '=', $value);
                        }
                    });
                }
            })->where(function ($q) use ($whereIn) {
                foreach ($whereIn as $single_condition) {
                    $q->orWhere(function ($q) use ($single_condition) {
                        foreach ($single_condition as $key => $value) {
                            $q->whereIn($key, $value);
                        }
                    });
                }
            })->groupBy($group_by)->paginate(10);
        }
        else {
            // DB::enableQueryLog();
            $locations = Location::sortable()->where(function ($q) use ($condition) {
                foreach ($condition as $single_condition) {
                    $q->orWhere(function ($q) use ($single_condition) {
                        foreach ($single_condition as $key => $value) {
                            $q->where($key, '=', $value);
                        }
                    });
                }
            })->where(function ($q) use ($whereIn) {
                foreach ($whereIn as $single_condition) {
                    $q->orWhere(function ($q) use ($single_condition) {
                        foreach ($single_condition as $key => $value) {
                            $q->whereIn($key, $value);
                        }
                    });
                }
            })
            ->paginate(10);
            // dd(DB::getQueryLog());

            if ($request->get('group_name')) {
                $location_ids = Location::select('id as location_id')->where(function ($q) use ($condition) {
                    foreach ($condition as $single_condition) {
                        $q->orWhere(function ($q) use ($single_condition) {
                            foreach ($single_condition as $key => $value) {
                                $q->where($key, '=', $value);
                            }
                        });
                    }
                })->get()->toArray();

                $locationGroup = new LocationGroup();

                $locationGroup->group_name = $request->get('group_name');
                $locationGroup->zip_codes = '';

                $locationGroup->save();

                array_walk($location_ids, function(&$a) use ($locationGroup) {
                    $a['location_group_id'] = $locationGroup->id;
                });
                
                foreach(array_chunk($location_ids, 1000) as $t) {
                    LocationGroupRecord::insert($t);
                }

                return redirect('/domain_report')->with('success', 'Group created successfully!');
            }
        }

        $industries = [];

        if ($request->get('generate_by') == 'Group Name') {
            $industries = Industries::select(['id', 'industry_name'])->get()->toArray();
        }
        
        return view('domain_report', compact('locations', 'uniqueCount', 'industries'));
    }

    public function allUniqueCities(Request $request) {
        $search_by = $request->get('search_by');
        $query = $request->get('query');

        $searchColumn = '';
        $searchColumnList = [];
        $groupBy = [];

        if ($search_by == 'Group Name') {
            $data = LocationGroup::select('group_name as info')->where('group_name', 'LIKE', '%'. $query. '%')->get()->toArray();

            return response()->json($data);
        }
        else if ($search_by == 'Country Name') {
            $searchColumn = 'country';
            $searchColumnList[] = 'country';
            $groupBy[] = 'country';
        }
        else if ($search_by == 'State Name') {
            $searchColumn = 'state_name';

            $searchColumnList[] = 'country';
            $searchColumnList[] = 'state_name';

            $groupBy[] = 'country';
            $groupBy[] = 'state_name';
        }
        else if ($search_by == 'County Name') {
            $searchColumn = 'county_name';

            $searchColumnList[] = 'country';
            $searchColumnList[] = 'state_name';
            $searchColumnList[] = 'county_name';

            $groupBy[] = 'country';
            $groupBy[] = 'state_name';
            $groupBy[] = 'county_name';
        }
        else if ($search_by == 'City Name') {
            $searchColumn = 'city_name';

            $searchColumnList[] = 'country';
            $searchColumnList[] = 'state_name';
            $searchColumnList[] = 'county_name';
            $searchColumnList[] = 'city_name';
            
            $groupBy[] = 'country';
            $groupBy[] = 'state_name';
            $groupBy[] = 'county_name';
            $groupBy[] = 'city_name';
        }

        $data = Location::select($searchColumnList)->where($searchColumn, 'LIKE', '%'. $query. '%')->groupBy($groupBy)->get()->toArray();

        $dataList = [];
        
        if ($search_by == 'Country Name') {
            foreach ($data as $details) {
                $dataList[] = ['country' => $details['country'], 'info' => $details['country']];
                // $dataList[] = $details['country'] . ', ' . $details['state_name'];
            }
        }
        else if ($search_by == 'State Name') {
            foreach ($data as $details) {
                $dataList[] = ['country' => $details['country'], 'state_name' => $details['state_name'], 'info' => $details['country'] . ', ' . $details['state_name']];
                // $dataList[] = $details['country'] . ', ' . $details['state_name'];
            }
        }
        else if ($search_by == 'County Name') {
            foreach ($data as $details) {
                $dataList[] = ['country' => $details['country'], 'state_name' => $details['state_name'], 'county_name' => $details['county_name'], 'info' => $details['country'] . ', ' . $details['state_name'] . ', ' . $details['county_name']];
                // $dataList[] = $details['country'] . ', ' . $details['state_name'] . ', ' . $details['county_name'];
            }
        }
        else if ($search_by == 'City Name') {
            foreach ($data as $details) {
                $dataList[] = ['country' => $details['country'], 'state_name' => $details['state_name'], 'county_name' => $details['county_name'], 'city_name' => $details['city_name'], 'info' => $details['country'] . ', ' . $details['state_name'] . ', County: ' . $details['county_name'] . ', ' . $details['city_name']];
                // $dataList[] = $details['country'] . ', ' . $details['state_name'] . ', County: ' . $details['county_name'] . ', ' . $details['city_name'];
            }
        }

        return response()->json($dataList);
    }

    public function getDomainByLocation(Request $request) {
        $location = preg_replace("/[^a-zA-Z0-9]+/", "", strtolower($request->post('location')));
        $keywords = preg_replace("/[^a-zA-Z0-9,]+/", "", strtolower($request->post('keywords')));

        $maintainOrder = (bool) $request->post('maintainOrder');
        $keywords = explode(',', $keywords);

        $tldList[] = '.com';
        $tldList[] = '.us';
        $tldList[] = '.info';

        $possible_combinations = Helpers::generateDomains($location, $keywords, $tldList, $maintainOrder);
        
        return response()->json($possible_combinations);
    }

    public function getZipCodes(Request $request) {
        $query = $request->get('query');

        $locationData = Location::select(['zip_code', 'id'])->where('zip_code', 'LIKE', $query. '%')->get()->toArray();

        return response()->json($locationData);
    }

    public function getLocationGroupZipCodes(Request $request) {
        $country = $request->post('country');
        $state = $request->post('state');
        $county = $request->post('county');
        $city = $request->post('city');

        $locationGroup = $request->post('locationGroup');

        $locationGroupDetails = LocationGroup::select('id')->where(['group_name' => $locationGroup])->first()->toArray();

        $condition['location_group_id'] = $locationGroupDetails['id'];

        $condition['country'] = $country;
        $condition['state_name'] = $state;
        $condition['county_name'] = $county;
        $condition['city_name'] = $city;

        $locationData = Location::select(['locations.zip_code', 'locations.id'])->join('location_group_records', 'location_group_records.location_id', '=', 'locations.id')->where($condition)->get()->toArray();

        return response()->json($locationData);
    }

    public function addZipCodes(Request $request) {
        $country = $request->post('country');
        $state = $request->post('state');
        $county = $request->post('county');
        $city = $request->post('city');

        $locationGroup = $request->post('locationGroup');
        $locations = $request->post('locations');

        $locationGroupDetails = LocationGroup::select('id')->where(['group_name' => $locationGroup])->first()->toArray();

        $condition['location_group_id'] = $locationGroupDetails['id'];

        $condition['country'] = $country;
        $condition['state_name'] = $state;
        $condition['county_name'] = $county;
        $condition['city_name'] = $city;

        $locationData = Location::select('locations.id')->join('location_group_records', 'location_group_records.location_id', '=', 'locations.id')->where($condition)->get()->toArray();

        $location_ids = array_column($locationData, 'id');
        
        LocationGroupRecord::where(['location_group_id' => $condition['location_group_id']])->whereIn('location_id', $location_ids)->delete();

        foreach ($locations as $locationDetails) {
            $record = new LocationGroupRecord();

            $record->location_group_id = $condition['location_group_id'];
            $record->location_id = $locationDetails['id'];
            $record->save();
        }
        
        return response()->json(['success' => 'true']);
    }

    public function saveSearchHistory(Request $request) {
        $type = $request->post('type');
        $title = $request->post('title');
        $param = $request->post('param');
        $other_param = $request->post('other_param');

        $searchHistory = new SearchHistory;

        $searchHistory->type = $type;
        $searchHistory->title = $title;
        $searchHistory->param = json_encode($param);
        $searchHistory->other_param = json_encode($other_param);

        $searchHistory->save();
        
        return response()->json(['success' => 'true']);
    }

    public function searchHistory(Request $request) {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');

        $search_by = $request->get('search_by');
        $show_favorite = $request->get('show_favorite');

        $condition = [];
        $conditionBetween = [];

        if ($start_date && $end_date) {
            $conditionBetween = [$start_date, $end_date];
        }

        if ($search_by) {
            $condition['title'] = $search_by;
        }
        if ($show_favorite) {
            $condition['is_favorite'] = 1;
        }

        if ($conditionBetween) {
            // DB::enableQueryLog();
            $model = SearchHistory::whereBetween(DB::raw('DATE(created_at)'), $conditionBetween)->where($condition)->sortable(['created_at' => 'desc'])->paginate(10);
            // dd(DB::getQueryLog());
        }
        else {
            $model = SearchHistory::where($condition)->sortable(['created_at' => 'desc'])->paginate(10);
        }
        
        return view('search_history', compact('model'));
    }

    public function deleteSearchHistory(Request $request) {    
        $ids = $request->post('ids');

        SearchHistory::whereIn('id', $ids)->delete();

        $request->session()->flash('success', 'Record deleted successfully.');

        return response()->json(['success' => 'true']);
    }

    public function searchHistoryToggleFavorite(Request $request) {    
        $id = $request->post('id');

        SearchHistory::where('id', $id)->first()->toggleFavorite();

        return response()->json(['success' => 'true']);
    }

    public function manageIndustries(Request $request) {
        $model = Industries::sortable(['created_at' => 'desc'])->paginate(10);

        return view('manage_industry', compact('model'));
    }

    public function addIndustry(Request $request) {
        $request->validate([
            'industry_name' => 'required|min:2|max:191',
        ]);

        $model = new Industries();
        $model->industry_name = $request->industry_name;

        $model->save();

        return redirect()->back()->with('success', 'Record saved successfully.');
    }

    public function deleteIndustry($id) {
        Industries::where('id', $id)->first()->delete();

        return redirect()->back()->with('error', 'Record deleted successfully.');
    }
}
