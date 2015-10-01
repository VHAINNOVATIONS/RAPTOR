using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using NHapi.Model.V24.Message;
using NHapi.Base.Parser;
using gov.va.medora.mdo.exceptions;
using NHapi.Model.V24.Segment;
using NHapi.Base.Model;
using NHapi.Model.V24.Datatype;
using gov.va.medora.mdo.src.mdo.dao.hl7;

namespace gov.va.medora.mdo.dao.hl7.rxRefill
{
    public class RxProfileDecoder
    {
        public RxProfileDecoder()
        {
        }

        public IList<Medication> parse(string message)
        {
            PipeParser parser = new PipeParser();
            IMessage msg = parser.Parse(message);

            if (msg.GetType() != typeof(RTB_K13))
            {
                throw new MdoException("Unexpected HL7 return type. Expected RTB_K13 but received " + msg.GetType().Name + ": " + message);
            }

            RTB_K13 rtb_k13 = (RTB_K13)msg;

            string responseStatus = rtb_k13.QAK.QueryResponseStatus.Value;

            if (String.Equals("NF", responseStatus, StringComparison.CurrentCultureIgnoreCase))
            {
                // TODO - see RxProfileDecoder.decode
                return null;
            }
            
            if (!String.Equals("OK", responseStatus, StringComparison.CurrentCultureIgnoreCase))
            {
                throw new MdoException("Received processing error: " + message);
            }

            int recordCount = Int32.Parse(rtb_k13.QAK.ThisPayload.Value);
            IList<Medication> meds = new List<Medication>();

            for (int i = 0; i < recordCount; i++)
            {
                RDT rdt = rtb_k13.ROW_DEFINITION.GetRDT(i);
                meds.Add(buildMedication(rdt));
            }

            return meds;
        }

        internal Medication buildMedication(RDT rdt)
        {
            Medication med = new Medication();
            med.OrderId = HL7Helper.getString(rdt, 13, 0);
            med.Refills = HL7Helper.getString(rdt, 11, 0);
            med.Quantity = HL7Helper.getString(rdt, 9, 0);
            med.DaysSupply = HL7Helper.getString(rdt, 10, 0);

            med.IssueDate = HL7Helper.getString(rdt, 4, 0);
            med.LastFillDate = HL7Helper.getString(rdt, 5, 0);
            med.StartDate = HL7Helper.getString(rdt, 6, 0);
            med.ExpirationDate = HL7Helper.getString(rdt, 7, 0);

            med.RxNumber = HL7Helper.getString(rdt, 1, 0);
            med.Id = HL7Helper.getString(rdt, 2, 0);

            med.Provider = new Author();
            NHapi.Base.Model.IType provider = ((Varies)rdt.GetField(12, 0)).Data;
            if (provider is GenericComposite)
            {
                GenericComposite gc = (GenericComposite)provider;
                NHapi.Base.Model.IType[] components = gc.Components;
                med.Provider.Id = ((Varies)components[0]).Data.ToString();
                med.Provider.Name = ((Varies)components[1]).Data.ToString() + ", " + ((Varies)components[2]).Data.ToString();
            }
            else
            {
                med.Provider.Id = provider.ToString();
            }

            med.Name = HL7Helper.getString(rdt, 3, 0);
            med.Sig = HL7Helper.getString(rdt, 20, 0);
            med.Detail = HL7Helper.getString(rdt, 19, 0);
            med.Status = HL7Helper.getString(rdt, 8, 0);

            return med;
        }

    }
}
