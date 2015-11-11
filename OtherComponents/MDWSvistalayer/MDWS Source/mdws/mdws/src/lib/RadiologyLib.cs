using System;
using System.Collections.Generic;
using gov.va.medora.mdws.dto;
using gov.va.medora.mdo.api;
using gov.va.medora.mdo;
using gov.va.medora.mdo.domain;
using gov.va.medora.utils;

namespace gov.va.medora.mdws
{
    public class RadiologyLib
    {
        MySession _mySession;

        public RadiologyLib(MySession mySession)
        {
            _mySession = mySession;
        }

        public RadiologyCancellationReasonArray getCancellationReasons()
        {
            RadiologyCancellationReasonArray result = new RadiologyCancellationReasonArray();

            String sessionCheck = MdwsUtils.isAuthorizedConnection(_mySession);
            if (!String.Equals(sessionCheck, "OK", StringComparison.CurrentCultureIgnoreCase))
            {
                result.fault = new FaultTO(sessionCheck);
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IList<RadiologyCancellationReason> reasons = new RadiologyApi().getCancellationsReasons(_mySession.ConnectionSet.BaseConnection);
                result = new RadiologyCancellationReasonArray(reasons);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public TaggedImagingExamArray getImagingExamsByPatient(String patientId)
        {
            TaggedImagingExamArray result = new TaggedImagingExamArray();

            String sessionCheck = MdwsUtils.isAuthorizedConnection(_mySession);
            if (!String.Equals(sessionCheck, "OK", StringComparison.CurrentCultureIgnoreCase))
            {
                result.fault = new FaultTO(sessionCheck);
            }
            
            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IList<ImagingExam> exams = new RadiologyApi().getExamsByPatient(_mySession.ConnectionSet.BaseConnection, patientId);
                result = new TaggedImagingExamArray(_mySession.ConnectionSet.BaseSiteId, exams);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public TextTO cancelImagingExam(String examIdentifier, String reasonIen, bool cancelAssociatedOrder, String holdDescription)
        {
            TextTO result = new TextTO();

            String sessionCheck = MdwsUtils.isAuthorizedConnection(_mySession);
            if (!String.Equals(sessionCheck, "OK", StringComparison.CurrentCultureIgnoreCase))
            {
                result.fault = new FaultTO(sessionCheck);
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                new RadiologyApi().cancelExam(_mySession.ConnectionSet.BaseConnection, examIdentifier, reasonIen, cancelAssociatedOrder, holdDescription);
                result.text = "OK";
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public TaggedTextArray getContrastMedia()
        {
            Dictionary<String, String> mediaDict = new RadiologyApi().getContrastMedia();
            return new TaggedTextArray(mediaDict);
        }

        public TaggedTextArray getComplications()
        {
            TaggedTextArray result = new TaggedTextArray();

            String sessionCheck = MdwsUtils.isAuthorizedConnection(_mySession);
            if (!String.Equals(sessionCheck, "OK", StringComparison.CurrentCultureIgnoreCase))
            {
                result.fault = new FaultTO(sessionCheck);
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Dictionary<String, String> complications = new RadiologyApi().getComplications(_mySession.ConnectionSet.BaseConnection);
                result = new TaggedTextArray(complications);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public ImagingExamTO registerExam(String orderId,
            String examDateTime,
            String examCategory,
            String hospitalLocation,
            String ward,
            String service,
            String technologistComment)
        {
            ImagingExamTO result = new ImagingExamTO();

            String sessionCheck = MdwsUtils.isAuthorizedConnection(_mySession);
            if (!String.Equals(sessionCheck, "OK", StringComparison.CurrentCultureIgnoreCase))
            {
                result.fault = new FaultTO(sessionCheck);
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                result.fault = new FaultTO("Not yet implemented");
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

        public OrderTO discontinueRadiologyOrder(String patientId, String orderIen, String providerDuz, String locationIen, String reasonIen)
        {
            OrderTO result = new OrderTO();
            String sessionCheck = MdwsUtils.isAuthorizedConnection(_mySession);
            if (!String.Equals(sessionCheck, "OK", StringComparison.CurrentCultureIgnoreCase))
            {
                result.fault = new FaultTO(sessionCheck);
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Order mdo = new RadiologyApi().discontinueOrder(_mySession.ConnectionSet.BaseConnection, patientId, orderIen, providerDuz, locationIen, reasonIen);
                result = new OrderTO(mdo);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public OrderTO discontinueAndSignRadiologyOrder(String patientId, String orderIen, String providerDuz, String locationIen, String reasonIen, String eSig)
        {
            OrderTO result = new OrderTO();
            String sessionCheck = MdwsUtils.isAuthorizedConnection(_mySession);
            if (!String.Equals(sessionCheck, "OK", StringComparison.CurrentCultureIgnoreCase))
            {
                result.fault = new FaultTO(sessionCheck);
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                Order mdo = new RadiologyApi().discontinueAndSignOrder(_mySession.ConnectionSet.BaseConnection, patientId, orderIen, providerDuz, locationIen, reasonIen, eSig);
                result = new OrderTO(mdo);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public TextTO signOrder(String orderId, String providerDuz, String locationIen, String eSig)
        {
            TextTO result = new TextTO();
            String sessionCheck = MdwsUtils.isAuthorizedConnection(_mySession);
            if (!String.Equals(sessionCheck, "OK", StringComparison.CurrentCultureIgnoreCase))
            {
                result.fault = new FaultTO(sessionCheck);
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                new RadiologyApi().signOrder(_mySession.ConnectionSet.BaseConnection, orderId, providerDuz, locationIen, eSig);
                result.text = "OK";
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }
            return result;
        }

        public List<OrderTypeTO> getImagingOrderTypes()
        {
            List<OrderTypeTO> result = new List<OrderTypeTO>();

            if ("OK" != MdwsUtils.isAuthorizedConnection(_mySession))
            {
                result.Add(new OrderTypeTO(new UnauthorizedAccessException("No active session. You need to login first")));
            }

            if (result.Count > 0)
            {
                return result;
            }

            try
            {
                IList<OrderType> mdos = new RadiologyApi().getImagingOrderTypes(_mySession.ConnectionSet.BaseConnection);
                foreach (OrderType ot in mdos)
                {
                    result.Add(new OrderTypeTO(ot));
                }
            }
            catch (Exception exc)
            {
                result.Add(new OrderTypeTO(exc));
            }

            return result;
        }

        public List<OrderTypeTO> getOrderableItems(String dialogId)
        {
            List<OrderTypeTO> result = new List<OrderTypeTO>();

            if ("OK" != MdwsUtils.isAuthorizedConnection(_mySession))
            {
                result.Add(new OrderTypeTO(new UnauthorizedAccessException("No active session. You need to login first")));
            }

            if (result.Count > 0)
            {
                return result;
            }

            try
            {
                IList<OrderType> mdos = new RadiologyApi().getOrderableItems(_mySession.ConnectionSet.BaseConnection, dialogId);
                foreach (OrderType ot in mdos)
                {
                    result.Add(new OrderTypeTO(ot));
                }
            }
            catch (Exception exc)
            {
                result.Add(new OrderTypeTO(exc));
            }

            return result;
        }

        public List<OrderCheckTO> getOrderChecks(String patientId, String orderStartStr, String locationId, String orderableItemId)
        {
            List<OrderCheckTO> result = new List<OrderCheckTO>();
            DateTime orderStartParsed = new DateTime();

            if ("OK" != MdwsUtils.isAuthorizedConnection(_mySession))
            {
                result.Add(new OrderCheckTO(new UnauthorizedAccessException("No active session. You need to login first")));
            }
            else if (String.IsNullOrEmpty(patientId) || String.IsNullOrEmpty(orderStartStr) || String.IsNullOrEmpty(locationId) || String.IsNullOrEmpty(orderableItemId))
            {
                result.Add(new OrderCheckTO(new ArgumentNullException("Must supply all arguments")));
            }
            else if (!DateTime.TryParse(orderStartStr, out orderStartParsed))
            {
                if (orderStartStr.StartsWith("2") && orderStartStr.Contains(".")) // looks like a yyyymmDD.HHmmss date/time string
                {
                    orderStartParsed = DateUtils.IsoDateStringToDateTime(orderStartStr);
                }
                else
                {
                    result.Add(new OrderCheckTO(new ArgumentException("Invalid order start date/time string")));
                }
            }

            if (result.Count > 0)
            {
                return result;
            }

            try
            {
                IList<OrderCheck> mdos = new RadiologyApi().getOrderChecks(_mySession.ConnectionSet.BaseConnection, patientId, orderStartParsed, locationId, orderableItemId);
                foreach (OrderCheck oc in mdos)
                {
                    result.Add(new OrderCheckTO(oc));
                }
            }
            catch (Exception exc)
            {
                result.Add(new OrderCheckTO(exc));
            }

            return result;
        }

        public RadiologyOrderDialogTO getRadiologyOrderDialog(String patientId, String dialogId)
        {
            RadiologyOrderDialogTO result = new RadiologyOrderDialogTO();
            if ("OK" != MdwsUtils.isAuthorizedConnection(_mySession))
            {
                result = new RadiologyOrderDialogTO(new UnauthorizedAccessException("No active session. You need to login first"));
            }
            else if (String.IsNullOrEmpty(patientId) || String.IsNullOrEmpty(dialogId))
            {
                result = new RadiologyOrderDialogTO(new ArgumentNullException("Must supply all arguments"));
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                RadiologyOrderDialog dialog = new RadiologyApi().getRadiologyDialogData(_mySession.ConnectionSet.BaseConnection, patientId, dialogId);
                result = new RadiologyOrderDialogTO(dialog);
            }
            catch (Exception exc)
            {
                result = new RadiologyOrderDialogTO(exc);
            }

            return result;
        }

        public OrderTO saveNewRadiologyOrder(
            String patientId,
            String duz,
            String locationIEN,
            String dlgDisplayGroupId,
            String orderableItemIen,
            String urgencyCode,
            String modeCode,
            String classCode,
            String contractSharingIen,
            String submitTo,
            String pregnant,
            String isolation,
            String reasonForStudy,
            String clinicalHx,
            String startDateTime,
            String preOpDateTime,
            String modifierIds,
            String eSig,
            String orderCheckOverrideReason)
        {
            OrderTO result = new OrderTO();

            if ("OK" != MdwsUtils.isAuthorizedConnection(_mySession))
            {
                result.fault = new FaultTO("No connection - need to login?");
            }

            if (result.fault != null)
            {
                return result;
            }

            try
            {
                IList<String> modifierIdList = new List<String>();
                if  (!String.IsNullOrEmpty(modifierIds))
                {
                    modifierIdList = new List<String>(StringUtils.split(modifierIds, StringUtils.STICK));
                }

                bool pregnantParsed = false;
                if (!String.IsNullOrEmpty(pregnant) && StringUtils.parseBool(pregnant))
                {
                    pregnantParsed = true;
                }
                bool isolationParsed = false;
                if (!String.IsNullOrEmpty(isolation) && StringUtils.parseBool(isolation))
                {
                    isolationParsed = true;
                }

                DateTime startDtParsed = DateUtils.IsoDateStringToDateTime(startDateTime);
                DateTime preOpDtParsed = DateUtils.IsoDateStringToDateTime(preOpDateTime);

                Order mdo = new RadiologyApi().saveNewOrder(_mySession.ConnectionSet.BaseConnection, patientId, duz, locationIEN, dlgDisplayGroupId, orderableItemIen,
                    urgencyCode, modeCode, classCode, contractSharingIen, submitTo, pregnantParsed, isolationParsed, reasonForStudy, clinicalHx, startDtParsed, preOpDtParsed, modifierIdList, eSig, orderCheckOverrideReason);

                result = new OrderTO(mdo);
            }
            catch (Exception exc)
            {
                result.fault = new FaultTO(exc);
            }

            return result;
        }

    }
}