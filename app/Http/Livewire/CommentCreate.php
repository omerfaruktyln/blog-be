<?php

namespace App\Http\Livewire;

use App\Models\Comment;
use App\Models\Post;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class CommentCreate extends Component
{
    public string $comment = ''; // Kullanıcının girdiği yorum
    public Post $post; // Yorum yapılacak gönderi
    public ?Comment $commentModel = null; // Düzenlenmekte olan yorum (varsa)
    public ?Comment $parentComment = null; // Cevap olarak verilen üst yorum (varsa)

    public function mount(Post $post, $commentModel = null, $parentComment = null)
    {
        $this->post = $post;
        $this->commentModel = $commentModel;
        $this->comment = $commentModel ? $commentModel->comment : ''; // Eğer bir yorum düzenleniyorsa, içeriği doldur

        $this->parentComment = $parentComment; // Eğer bir cevap yorum varsa, onu ata
    }

    public function render()
    {
        return view('livewire.comment-create');
    }

    public function createComment()
    {
        $user = Auth::user(); // Kullanıcıyı kimlik doğrulama ile alın
        if (!$user) {
            return $this->redirect('/login'); // Kullanıcı giriş yapmamışsa giriş sayfasına yönlendir
        }

        if ($this->commentModel) {
            // Yorum düzenleme işlemi
            if ($this->commentModel->user_id != $user->id) {
                return response('Bu işlemi gerçekleştirme yetkiniz yok', 403); // Başkasının yorumunu düzenlemeye çalışırsa
            }

            $this->commentModel->comment = $this->comment;
            $this->commentModel->save();

            $this->comment = ''; // Yorum alanını temizle
            $this->emitUp('commentUpdated'); // Ana bileşene "commentUpdated" olayını yay
        } else {
            // Yeni yorum oluşturma işlemi
            $comment = Comment::create([
                'comment' => $this->comment,
                'post_id' => $this->post->id,
                'user_id' => $user->id,
                'parent_id' => $this->parentComment?->id,
                'is_approved' => false, // Başlangıçta onaylanmamış olarak kaydediliyor
            ]);

            $this->emitUp('commentCreated', $comment->id); // Ana bileşene "commentCreated" olayını yay
            $this->comment = ''; // Yorum alanını temizle
        }
    }
}
