<?php

namespace App\Http\Controllers;

use App\Enums\LetterType;
use App\Http\Requests\StoreLetterRequest;
use App\Http\Requests\UpdateLetterRequest;
use App\Models\Attachment;
use App\Models\Classification;
use App\Models\Config;
use App\Models\Letter;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class IncomingLetterController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return View
     */
    public function index(Request $request): View
    {
        $query = Letter::incoming();  // Mendapatkan surat masuk

        // Jika role pengguna adalah staff, filter surat berdasarkan user_id
        if (auth()->user()->role == 'staff') {
            $query->where('user_id', auth()->user()->id);
        }

        return view('pages.transaction.incoming.index', [
            'data' => $query->render($request->search),
            'search' => $request->search,
        ]);
    }

    /**
     * Display a listing of the incoming letter agenda.
     *
     * @param Request $request
     * @return View
     */
    public function agenda(Request $request): View
    {
        $query = Letter::incoming()->agenda($request->since, $request->until, $request->filter);  // Mendapatkan surat masuk dengan agenda

        // Jika role pengguna adalah staff, filter surat berdasarkan user_id
        if (auth()->user()->role == 'staff') {
            $query->where('user_id', auth()->user()->id);
        }

        return view('pages.transaction.incoming.agenda', [
            'data' => $query->render($request->search),
            'search' => $request->search,
            'since' => $request->since,
            'until' => $request->until,
            'filter' => $request->filter,
            'query' => $request->getQueryString(),
        ]);
    }

    /**
     * @param Request $request
     * @return View
     */
    public function print(Request $request): View
    {
        $agenda = __('menu.agenda.menu');
        $letter = __('menu.agenda.incoming_letter');
        $title = App::getLocale() == 'id' ? "$agenda $letter" : "$letter $agenda";
        
        $query = Letter::incoming()->agenda($request->since, $request->until, $request->filter);  // Mendapatkan surat masuk untuk print

        // Jika role pengguna adalah staff, filter surat berdasarkan user_id
        if (auth()->user()->role == 'staff') {
            $query->where('user_id', auth()->user()->id);
        }

        return view('pages.transaction.incoming.print', [
            'data' => $query->get(),
            'search' => $request->search,
            'since' => $request->since,
            'until' => $request->until,
            'filter' => $request->filter,
            'config' => Config::pluck('value','code')->toArray(),
            'title' => $title,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return View
     */
    public function create(): View
    {
        return view('pages.transaction.incoming.create', [
            'classifications' => Classification::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreLetterRequest $request
     * @return RedirectResponse
     */
    public function store(StoreLetterRequest $request): RedirectResponse
    {
        try {
            $user = auth()->user();

            if ($request->type != LetterType::INCOMING->type()) throw new \Exception(__('menu.transaction.incoming_letter'));

            $newLetter = $request->validated();
            $newLetter['user_id'] = $user->id;  // Menyimpan user_id saat surat disimpan
            $letter = Letter::create($newLetter);

            if ($request->hasFile('attachments')) {
                foreach ($request->attachments as $attachment) {
                    $extension = $attachment->getClientOriginalExtension();
                    if (!in_array($extension, ['png', 'jpg', 'jpeg', 'pdf'])) continue;
                    $filename = time() . '-'. $attachment->getClientOriginalName();
                    $filename = str_replace(' ', '-', $filename);
                    $attachment->storeAs('public/attachments', $filename);
                    Attachment::create([
                        'filename' => $filename,
                        'extension' => $extension,
                        'user_id' => $user->id,
                        'letter_id' => $letter->id,
                    ]);
                }
            }

            return redirect()
                ->route('transaction.incoming.index')
                ->with('success', __('menu.general.success'));
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Letter $incoming
     * @return View
     */
    public function show(Letter $incoming): View
    {
        // Pastikan hanya staff yang melihat surat yang dibuat oleh mereka sendiri
        if (auth()->user()->role == 'staff' && auth()->user()->id !== $incoming->user_id) {
            abort(403, 'You are not authorized to view this letter');
        }

        return view('pages.transaction.incoming.show', [
            'data' => $incoming->load(['classification', 'user', 'attachments']),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param Letter $incoming
     * @return View
     */
    public function edit(Letter $incoming): View
    {
        // Pastikan hanya staff yang bisa mengedit surat yang mereka buat
        if (auth()->user()->role == 'staff' && auth()->user()->id !== $incoming->user_id) {
            abort(403, 'You are not authorized to edit this letter');
        }

        return view('pages.transaction.incoming.edit', [
            'data' => $incoming,
            'classifications' => Classification::all(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateLetterRequest $request
     * @param Letter $incoming
     * @return RedirectResponse
     */
    public function update(UpdateLetterRequest $request, Letter $incoming): RedirectResponse
    {
        try {
            // Pastikan hanya staff yang mengedit surat yang mereka buat
            if (auth()->user()->role == 'staff' && auth()->user()->id !== $incoming->user_id) {
                abort(403, 'You are not authorized to edit this letter');
            }

            $incoming->update($request->validated());

            if ($request->hasFile('attachments')) {
                foreach ($request->attachments as $attachment) {
                    $extension = $attachment->getClientOriginalExtension();
                    if (!in_array($extension, ['png', 'jpg', 'jpeg', 'pdf'])) continue;
                    $filename = time() . '-'. $attachment->getClientOriginalName();
                    $filename = str_replace(' ', '-', $filename);
                    $attachment->storeAs('public/attachments', $filename);
                    Attachment::create([
                        'filename' => $filename,
                        'extension' => $extension,
                        'user_id' => auth()->user()->id,
                        'letter_id' => $incoming->id,
                    ]);
                }
            }

            return back()->with('success', __('menu.general.success'));
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Letter $incoming
     * @return RedirectResponse
     */
    public function destroy(Letter $incoming): RedirectResponse
    {
        try {
            // Pastikan hanya admin atau creator surat yang bisa menghapus surat
            if (auth()->user()->role == 'staff' && auth()->user()->id !== $incoming->user_id) {
                abort(403, 'You are not authorized to delete this letter');
            }

            $incoming->delete();

            return redirect()
                ->route('transaction.incoming.index')
                ->with('success', __('menu.general.success'));
        } catch (\Throwable $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }
}
