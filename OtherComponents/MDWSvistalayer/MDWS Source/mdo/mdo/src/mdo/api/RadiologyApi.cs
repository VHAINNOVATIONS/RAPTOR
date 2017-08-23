using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using gov.va.medora.mdo.dao;
using gov.va.medora.mdo.domain;

namespace gov.va.medora.mdo.api
{
    public class RadiologyApi
    {
        public String _radiologyDao = "IRadiologyDao";

        public Order discontinueOrder(AbstractConnection cxn, String patientId, string orderIen, string providerDuz, String locationIen, string reasonIen)
        {
            return ((IRadiologyDao)cxn.getDao(_radiologyDao)).discontinueOrder(patientId, orderIen, providerDuz, locationIen, reasonIen);
        }

        public Order discontinueAndSignOrder(AbstractConnection cxn, String patientId, string orderIen, string providerDuz, String locationIen, string reasonIen, String eSig)
        {
            return ((IRadiologyDao)cxn.getDao(_radiologyDao)).discontinueAndSignOrder(patientId, orderIen, providerDuz, locationIen, reasonIen, eSig);
        }
        
        public void signOrder(AbstractConnection cxn, String orderId, String providerDuz, String locationIen, String eSig)
        {
            ((IRadiologyDao)cxn.getDao(_radiologyDao)).signOrder(orderId, providerDuz, locationIen, eSig);
        }

        public IList<ImagingExam> getExamsByPatient(AbstractConnection cxn, String patientId)
        {
            return ((IRadiologyDao)cxn.getDao(_radiologyDao)).getExamsByPatient(patientId);
        }

        public IList<RadiologyCancellationReason> getCancellationsReasons(AbstractConnection cxn)
        {
            return ((IRadiologyDao)cxn.getDao(_radiologyDao)).getCancellationReasons();
        }

        public void cancelExam(AbstractConnection cxn, String examIdentifier, String reasonIen, bool cancelAssociatedOrder = true, String holdDescription = null)
        {
            ((IRadiologyDao)cxn.getDao(_radiologyDao)).cancelExam(examIdentifier, reasonIen, cancelAssociatedOrder, holdDescription);
        }

        public Dictionary<String, String> getContrastMedia()
        {
            return new gov.va.medora.mdo.dao.vista.VistaRadiologyDao(null).getContrastMedia();
        }

        public Dictionary<String, String> getComplications(AbstractConnection cxn)
        {
            return ((IRadiologyDao)cxn.getDao(_radiologyDao)).getComplications();
        }

        public ImagingExam registerExam(AbstractConnection cxn,
            String orderId,
            String examDateTime,
            String examCategory,
            String hospitalLocation,
            String ward,
            String service,
            String technologistComment)
        {
            return ((IRadiologyDao)cxn.getDao(_radiologyDao)).registerExam(orderId, examDateTime, examCategory, hospitalLocation, ward, service, technologistComment);
        }

        public IList<OrderType> getImagingOrderTypes(AbstractConnection cxn)
        {
            return new gov.va.medora.mdo.dao.vista.VistaOrdersDao(cxn).getImagingOrderTypes();
        }

        public RadiologyOrderDialog getRadiologyDialogData(AbstractConnection cxn, String patientId, String dialogId)
        {
            return new gov.va.medora.mdo.dao.vista.VistaOrdersDao(cxn).getRadiologyDialogData(patientId, dialogId);
        }

        public IList<OrderType> getOrderableItems(AbstractConnection cxn, String dialogId)
        {
            return new gov.va.medora.mdo.dao.vista.VistaOrdersDao(cxn).getRadiologyOrderableItems(dialogId);
        }

        public IList<OrderCheck> getOrderChecks(AbstractConnection cxn, String patientId, DateTime orderStart, String locationId, String orderableItemId)
        {
            return new gov.va.medora.mdo.dao.vista.VistaOrdersDao(cxn).getRadiologyOrderChecksForAcceptOrder(patientId, orderStart, locationId, orderableItemId);
        }

        public Order saveNewOrder(AbstractConnection cxn,
            String patientId,
            string duz,
            string locationIEN,
            //string dlgBaseName,
            String dlgDisplayGroupId,
            String orderableItemIen,
            String urgencyCode, // e.g. 9 => ROUTINE
            String modeCode, // e.g. P => PORTABLE
            String classCode, // e.g. O => OUTPATIENT
            String contractSharingIen,
            String submitTo, // e.g. 12 -> CT SCAN
            bool pregnant, // blank if male, 1/0 if female
            bool isolation, // set to 1 if true, 0 if false
            String reasonForStudy,
            String clinicalHx,
            DateTime startDateTime,
            DateTime preOpDateTime,
            IList<String> modifierIds,
            String eSig,
            String orderCheckOverrideReason)
        {
            return new gov.va.medora.mdo.dao.vista.VistaOrdersDao(cxn).saveNewRadiologyOrderWithBusinessRules(patientId, duz, locationIEN, dlgDisplayGroupId, orderableItemIen,
                urgencyCode, modeCode, classCode, contractSharingIen, submitTo, pregnant, isolation, reasonForStudy, clinicalHx, startDateTime, preOpDateTime, modifierIds, eSig, orderCheckOverrideReason);
        }

    }
}
