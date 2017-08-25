using System;
using System.Collections.Generic;
using System.Text;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws.dto
{
    public class OrderTO : AbstractTO
    {
        public string id;
        public string timestamp;
        public string orderingServiceName;
        public string treatingSpecialty;
        public string startDate;
        public string stopDate;
        public string status;
        public string sigStatus;
        public string dateSigned;
        public string verifyingNurse;
        public string dateVerified;
        public string verifyingClerk;
        public string chartReviewer;
        public string dateReviewed;
        public UserTO provider;
        public string text;
        public string detail;
        public string errMsg;
        public bool flag;
        public OrderTypeTO type;

        public OrderTO() { }

        public OrderTO(Order mdoOrder)
        {
            if (mdoOrder == null)
            {
                return;
            }
            this.id = mdoOrder.Id;
            this.timestamp = mdoOrder.Timestamp.ToString("yyyyMMdd.HHmmss");
            this.orderingServiceName = mdoOrder.OrderingServiceName;
            this.treatingSpecialty = mdoOrder.TreatingSpecialty;
            this.startDate = mdoOrder.StartDate.ToString("yyyyMMdd.HHmmss");
            this.stopDate = mdoOrder.StopDate.ToString("yyyyMMdd.HHmmss");
            this.status = mdoOrder.Status;
            this.sigStatus = mdoOrder.SigStatus;
            this.dateSigned = mdoOrder.DateSigned.ToString("yyyyMMdd.HHmmss");
            this.verifyingNurse = mdoOrder.VerifyingNurse;
            this.dateVerified = mdoOrder.DateVerified.ToString("yyyyMMdd.HHmmss");
            this.verifyingClerk = mdoOrder.VerifyingClerk;
            this.chartReviewer = mdoOrder.ChartReviewer;
            this.dateReviewed = mdoOrder.DateReviewed.ToString("yyyyMMdd.HHmmss");
            this.provider = new UserTO(mdoOrder.Provider);
            this.text = mdoOrder.Text;
            this.detail = mdoOrder.Detail;
            this.errMsg = mdoOrder.ErrMsg;
            this.flag = mdoOrder.Flag;
            this.type = new OrderTypeTO(mdoOrder.Type);
        }

        public OrderTO(Exception e)
        {
            this.fault = new FaultTO(e);
        }
    }
}
