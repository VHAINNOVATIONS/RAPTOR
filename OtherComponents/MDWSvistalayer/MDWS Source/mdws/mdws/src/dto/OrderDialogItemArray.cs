using System;
using System.Collections.Generic;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class OrderDialogItemArray : AbstractArrayTO
    {
        public OrderDialogItemTO[] items;

        public OrderDialogItemArray() { }

        public OrderDialogItemArray(OrderDialogItem[] mdo)
        {
            if (mdo == null)
            {
                return;
            }
            items = new OrderDialogItemTO[mdo.Length];
            for (int i = 0; i < mdo.Length; i++)
            {
                items[i] = new OrderDialogItemTO(mdo[i]);
            }
            count = mdo.Length;
        }
    }
}