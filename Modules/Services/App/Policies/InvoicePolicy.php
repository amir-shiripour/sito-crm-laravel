<?php

namespace Modules\Services\App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;
use Modules\Services\App\Http\Models\Invoice;
use Illuminate\Auth\Access\HandlesAuthorization;

class InvoicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user)
    {
        return $user->can('services.invoices.view')
            || $user->can('services.invoices.view.all');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Invoice $invoice
     * @return Response|bool
     */
    public function view(User $user, Invoice $invoice)
    {
        if ($user->can('services.invoices.view.all')) {
            return true;
        }

        if ($user->can('services.invoices.view')) {
            return (int)$invoice->created_by === (int)$user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user)
    {
        return $user->can('services.invoices.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Invoice $invoice
     * @return Response|bool
     */
    public function update(User $user, Invoice $invoice)
    {
        return $user->can('services.invoices.edit');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Invoice $invoice
     * @return Response|bool
     */
    public function delete(User $user, Invoice $invoice)
    {
        // Keep the logic for not deleting final invoices
        if ($invoice->invoice_number) {
            return false;
        }
        return $user->can('services.invoices.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Invoice $invoice
     * @return Response|bool
     */
    public function restore(User $user, Invoice $invoice)
    {
        return $user->can('services.invoices.manage');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Invoice $invoice
     * @return Response|bool
     */
    public function forceDelete(User $user, Invoice $invoice)
    {
        return $user->can('services.invoices.delete');
    }

    /**
     * Determine whether the user can convert a proforma to an invoice.
     *
     * @param User $user
     * @return Response|bool
     */
    public function convertToInvoice(User $user)
    {
        return $user->can('services.invoices.convert');
    }

    /**
     * Determine whether the user can record payment.
     */
    public function pay(User $user, Invoice $invoice)
    {
        return $user->can('services.invoices.pay');
    }

    /**
     * Determine whether the user can cancel an invoice.
     */
    public function cancel(User $user, Invoice $invoice)
    {
        return $user->can('services.invoices.cancel');
    }

    /**
     * Determine whether the user can cancel a payment.
     */
    public function cancelPayment(User $user, Invoice $invoice)
    {
        return $user->can('services.invoices.cancel-payment');
    }
}
