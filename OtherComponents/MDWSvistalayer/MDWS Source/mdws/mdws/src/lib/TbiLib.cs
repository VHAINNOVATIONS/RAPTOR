using System;
using gov.va.medora.mdws.dto;

namespace gov.va.medora.mdws
{
    public class TbiLib
    {
        MySession mySession;

        public TbiLib(MySession mySession)
        {
            this.mySession = mySession;
        }

        public TaggedConsultArray getConsultsForPatient()
        {
            TaggedConsultArray result = new TaggedConsultArray();

            try
            {
                OrdersLib lib = new OrdersLib(mySession);
                TaggedConsultArrays ta = lib.getConsultsForPatient();
                if (ta.fault != null)
                {
                    result.fault = ta.fault;
                    return result;
                }
                result = ta.arrays[0];
            }
            catch (Exception e)
            {
                result.fault = new FaultTO(e.Message);
            }
            return result;
        }

    }
}
