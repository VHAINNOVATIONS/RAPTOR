using System;
using System.Web;
using System.Collections.Specialized;
using System.Collections.Generic;
using gov.va.medora.mdo;
using gov.va.medora.mdo.api;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    public class OrdersLib
    {
        MySession mySession;

        public OrdersLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public TaggedConsultArrays getConsultsForPatient()
        {
            TaggedConsultArrays result = new TaggedConsultArrays();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = Consult.getConsultsForPatient(mySession.ConnectionSet);
                return new TaggedConsultArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
                return result;
            }
        }

        public OrderTO writeSimpleOrderByPolicy(string providerDUZ, string esig, string locationIEN, string orderIEN, string startDate)
        {
            OrderTO result = new OrderTO();

            if (!mySession.ConnectionSet.IsAuthorized)
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (mySession.Patient == null)
            {
                result.fault = new FaultTO("No patient selected. Need to select patient first.");
            }
            else if (String.IsNullOrEmpty(providerDUZ) || string.IsNullOrEmpty(esig) ||
                String.IsNullOrEmpty(locationIEN) || String.IsNullOrEmpty(startDate))
            {
                result.fault = new FaultTO("Failed to supply required parameters");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Order order = Order.writeSimpleOrderByPolicy(mySession.ConnectionSet.getConnection(mySession.ConnectionSet.BaseSiteId), 
                    mySession.Patient, providerDUZ, esig, locationIEN, orderIEN, 
                    gov.va.medora.utils.DateUtils.IsoDateStringToDateTime(startDate));
                return new OrderTO(order);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public OrderTO discontinueOrder()
        {
            throw new Exception("Not yet implemented");
        }

        public TaggedTextArray getDiscontinueReasons()
        {
            TaggedTextArray result = new TaggedTextArray();

            if (MdwsUtils.isAuthorizedConnection(mySession) == "OK")
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                MedsApi medsApi = new MedsApi();
                IndexedHashtable ht = medsApi.getDiscontinueReasons(mySession.ConnectionSet);
                return new TaggedTextArray(ht);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public TaggedOrderArrays getOrdersForPatient()
        {
            TaggedOrderArrays result = new TaggedOrderArrays();

            if(!(MdwsUtils.isAuthorizedConnection(mySession) == "OK"))
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (mySession.Patient == null)
            {
                result.fault = new FaultTO("No patient selected", "Need to select patient");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IndexedHashtable t = Order.getOrdersForPatient(mySession.ConnectionSet);
                result = new TaggedOrderArrays(t);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public TaggedTextArray getOrderableItemsByName(string name)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!(MdwsUtils.isAuthorizedConnection(mySession) == "OK"))
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(name))
            {
                result.fault = new FaultTO("Empty name");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                OrderedDictionary d = Order.getOrderableItemsByName(mySession.ConnectionSet.BaseConnection, name);
                result = new TaggedTextArray(d);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public TextTO getOrderStatusForPatient(string pid, string orderableItemId)
        {
            TextTO result = new TextTO();

            if (!(MdwsUtils.isAuthorizedConnection(mySession) == "OK"))
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(pid))
            {
                result.fault = new FaultTO("Empty PID");
            }
            else if (String.IsNullOrEmpty(orderableItemId))
            {
                result.fault = new FaultTO("Empty Orderable Item ID");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                string s = Order.getOrderStatusForPatient(mySession.ConnectionSet.BaseConnection, pid, orderableItemId);
                result = new TextTO(s);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public TaggedTextArray getOrderDialogsForDisplayGroup(string displayGroupId)
        {
            TaggedTextArray result = new TaggedTextArray();

            if (!(MdwsUtils.isAuthorizedConnection(mySession) == "OK"))
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(displayGroupId))
            {
                result.fault = new FaultTO("Empty Display Group ID");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                OrderedDictionary d = Order.getOrderDialogsForDisplayGroup(mySession.ConnectionSet.BaseConnection, displayGroupId);
                result = new TaggedTextArray(d);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public OrderDialogItemArray getOrderDialogItems(string dialogId)
        {
            OrderDialogItemArray result = new OrderDialogItemArray();

            if (!(MdwsUtils.isAuthorizedConnection(mySession) == "OK"))
            {
                result.fault = new FaultTO("Connections not ready for operation", "Need to login?");
            }
            else if (String.IsNullOrEmpty(dialogId))
            {
                result.fault = new FaultTO("Empty Dialog ID");
            }
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                List<OrderDialogItem> lst = Order.getOrderDialogItems(mySession.ConnectionSet.BaseConnection, dialogId);
                if (lst == null || lst.Count == 0)
                {
                    return result;
                }
                result.items = new OrderDialogItemTO[lst.Count];
                for (int i = 0; i < lst.Count; i++)
                {
                    result.items[i] = new OrderDialogItemTO(lst[i]);
                }
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }
    }
}
