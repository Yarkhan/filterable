<?php
namespace App;

trait Filterable{

	public function scopeFilter($query,$request){
		if(!$request) return $query;

		foreach($request as $key => $val){
			/**
			 * actually whereHas. We loop through each value and organize it
			 * in a way so that we can pass to scopeFilter in the related model
			 */
			if($key == 'has') {
				$queries = [];
				foreach($val as $key => $val){
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
			if($key == 'with'){
				if(!is_array($val)) $val = [$val];
				$query->with(...array_map(function($a){
					return str_replace(':','.',$a);
				},$val));
				continue;
			}
			/**
			 * Dead simple here on...
			 */
			if($key == 'limit'){
				$query->limit($val);
				continue;
			}
			if($key == 'offset'){
				$query->offset($val);
				continue;
			}
			if($key == 'orderBy'){
				$query->orderBy(explode(',',$val));
				continue;
			}
			if($key == 'fields'){
				$query->select(explode(',',$val));
				continue;
			}
			/**
			 * Common where() operations
			 */
			$operations = ['eq','gt','lt','bt','like'];

			/**
			 * where()
			 * we search for the "_operation" suffix in the key names
			 * if not there, query and bail out
			 */
			$parts = explode('_',$key);
			if(!in_array(end($parts),$operations)){
				if(!is_array($val)) $val = [$val];
				$query->where($key,...$val);
				continue;
			}

			/**
			 * We found the operation. Just query and walk away
			 */
			$op = array_splice($parts,-1,1)[0];
			$column = implode('_',$parts);

			$op == 'gt' and $query->where($column,'>',$val);

			$op == 'lt' and $query->where($column,'<',$val);

			$op == 'like' and $query->where($column,'like',$val);

			$op == 'bt' and	$query->whereBetween($column,explode(',',$val));

		}

		return $query;
	}
}
