# filterable
Trait for Eloquent models.


```php
//Post.php
class Post extends Model{
    use Filterable; 
}
```

```php
//PostController.php
class PostController extends Controller{
    public function index(Request $request){
        return Post::filter($request->all())->get();
    }
}
```

| Request | Query Equivalent     |
| :------------------------------------ | :--------------------------------         |
| example.com/api/posts?id=1                            | $postModel->where('id',1)                 |
| example.com/api/posts?id_gt=1                         | $postModel->where('id', > , 1 )           |
| example.com/api/posts?id_lt=1                         | $postModel->where('id', < , 1 )           |
| example.com/api/posts?title_like=title                | $postModel->where('id','like','title')    |
| example.com/api/posts?views_bt=100,200                | $postModel->whereBetween('views',[100,200])|
| example.com/api/posts?limit=1                         | $postModel->limit(1)                      |
| example.com/api/posts?offset=1                        | $postModel->offset(1)                     |
| example.com/api/posts?fields=id,title                 | $postModel->select('id','title')          |
| example.com/api/posts?with=comments                   | $postModel->with('comments')              |
| example.com/api/posts?with=comments:author            | $postModel->with('comments.author')       |
| example.com/api/posts?with[]=comments&with[]=author   | $postModel->with('comments','author')     |

## Bonus 
### Has 

```php
//example.com/api/posts?has[author.id]=1
//becomes...
$postModel->whereHas('author',function($query) use($args){
    $query->filter($args);
});
```
