using System;
using System.Collections.Generic;
using System.Collections.Specialized;
using System.Text;

namespace gov.va.medora.mdo.dao
{
    public interface IOrdersDao
    {
        Order[] getOrdersForPatient();
        Order[] getOrdersForPatient(string pid);
        OrderedDictionary getOrderableItemsByName(string name);
        string getOrderStatusForPatient(string dfn, string orderableItemId);
        OrderedDictionary getOrderDialogsForDisplayGroup(string displayGroupId);
        List<OrderDialogItem> getOrderDialogItems(string dialogId);
        Order writeSimpleOrderByPolicy(
            Patient patient,
            string duz,
            string esig,
            string locationIen,
            string orderIen,
            DateTime startDate);
    }
}
