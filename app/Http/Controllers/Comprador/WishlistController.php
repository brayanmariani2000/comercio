<?php
namespace App\Http\Controllers\Comprador;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = $request->user()->wishlists()->with('productos.imagenes')->get();
        return response()->json(['success' => true, 'wishlists' => $wishlists]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string',
            'publica' => 'boolean',
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

        $wishlist = $request->user()->wishlists()->create($request->only('nombre', 'publica'));
        return response()->json(['success' => true, 'wishlist' => $wishlist], 201);
    }

    public function addToWishlist(Request $request, $wishlistId)
    {
        $validator = Validator::make($request->all(), [
            'producto_id' => 'required|exists:productos,id'
        ]);

        if ($validator->fails()) return response()->json(['success' => false, 'errors' => $validator->errors()], 422);

        $wishlist = $request->user()->wishlists()->findOrFail($wishlistId);
        $wishlist->agregarProducto($request->producto_id);

        return response()->json(['success' => true, 'message' => 'Producto agregado a la lista']);
    }

    public function removeProduct(Request $request, $wishlistId, $productoId)
    {
        $wishlist = $request->user()->wishlists()->findOrFail($wishlistId);
        $wishlist->eliminarProducto($productoId);
        return response()->json(['success' => true, 'message' => 'Producto eliminado']);
    }
}