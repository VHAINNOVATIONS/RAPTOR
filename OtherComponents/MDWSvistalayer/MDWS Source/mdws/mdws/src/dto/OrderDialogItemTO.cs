using System;
using System.Collections.Generic;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class OrderDialogItemTO : AbstractTO
    {
        public int sequenceNumber;
        public char dataType;
        public string domain;
        public string displayText;
        public string orderableItemId;

        public OrderDialogItemTO() { }

        public OrderDialogItemTO(OrderDialogItem mdo)
        {
            this.sequenceNumber = mdo.SequenceNumber;
            this.dataType = mdo.DataType;
            this.domain = mdo.Domain;
            this.displayText = mdo.DisplayText;
            this.orderableItemId = mdo.OrderableItemId;
        }
    }
}