using System;
using System.Collections.Generic;
using System.Text;

namespace gov.va.medora.mdo
{
    public class OrderDialogItem
    {
        int seqNum;
        char dataType;
        string domain;
        string displayText;
        string orderableItemId;

        public OrderDialogItem() { }

        public int SequenceNumber
        {
            get { return seqNum; }
            set { seqNum = value; }
        }

        public char DataType
        {
            get { return dataType; }
            set { dataType = value; }
        }

        public string Domain
        {
            get { return domain; }
            set { domain = value; }
        }

        public string DisplayText
        {
            get { return displayText; }
            set { displayText = value; }
        }

        public string OrderableItemId
        {
            get { return orderableItemId; }
            set { orderableItemId = value; }
        }
    }
}
