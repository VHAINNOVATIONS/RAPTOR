using System;
using System.Collections.Specialized;
using gov.va.medora.mdws.dto;
using gov.va.medora.mdo;

namespace gov.va.medora.mdws
{
    public class VitalsLib
    {
        MySession mySession;

        public VitalsLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public TaggedVitalSignSetArrays getVitalSigns()
        {
            TaggedVitalSignSetArrays result = new TaggedVitalSignSetArrays();

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
                IndexedHashtable t = VitalSignSet.getVitalSigns(mySession.ConnectionSet);
                result = new TaggedVitalSignSetArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

        public TaggedVitalSignArrays getLatestVitalSigns()
        {
            TaggedVitalSignArrays result = new TaggedVitalSignArrays();

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
                IndexedHashtable t = VitalSign.getLatestVitalSigns(mySession.ConnectionSet);
                result = new TaggedVitalSignArrays(t);
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e);
            }
            return result;
        }

    }
}
