<?php
namespace App;

trait Filterable{

	public function scopeFilter($query,$request){
		if(!$request) return $query;

		foreach($request as $operation => $arguments){
			/**
			 * actually whereHas. We loop through each value and organize it
			 * in a way so that we can pass to scopeFilter in the related model
			 */
			if($operation == 'has') {
				$queries = [];
				foreach($arguments as $key => $val){
					list($related,$field) = explode('.',$key);
					if(!isset($queries[$related])) $queries[$related] = [];
					$queries[$related][$field] = $val;
				}
				foreach($queries as $relation => $fields){
					$query->whereHas($relation,function($query) use($fields){
						$query->Filter($fields);
					});
				}
				continue;
			}
			/**
			 * with() operation. Converts $val to array if it isn't.
			 * replaces ":" to "." to allow ("model.otherModel")
			 */
			if($operation == 'with'){
				if(!is_array($arguments)) $arguments = [$arguments];
				$query->with(...array_map(function($a){
					return str_replace(':','.',$a);
				},$arguments));
				continue;
			}
			/**
			 * Dead simple here on...
			 */
			if($operation == 'limit'){
				$query->limit($arguments);
				continue;
			}
			if($operation == 'offset'){
				$query->offset($arguments);
				continue;
			}
			if($operation == 'orderBy'){
				$query->orderBy(explode(',',$arguments));
				continue;
			}
			if($operation == 'fields'){
				$query->select(explode(',',$arguments));
				continue;
			}

			/**
			 * where()
			 * we search for the "_$operations" suffix in the key names
			 * if not there, query and bail out
			 */
			$operations = ['eq','gt','lt','bt','like'];
			$parts = explode('_',$operation);
			if(!in_array(end($parts),$operations)){
				if(!is_array($arguments)) $arguments = [$arguments];
				$query->where($operation,...$arguments);
				continue;
			}

			/**
			 * We found the operation. Just query and walk away
			 */
			$op = array_splice($parts,-1,1)[0];
			$column = implode('_',$parts);

			$op == 'gt' and $query->where($column,'>',$arguments);

			$op == 'lt' and $query->where($column,'<',$arguments);

			$op == 'like' and $query->where($column,'like',$arguments);

			$op == 'bt' and	$query->whereBetween($column,explode(',',$arguments));

		}

		return $query;
	}
}
