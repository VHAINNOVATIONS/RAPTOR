using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using NHapi.Model.V24.Message;
using NHapi.Base.Parser;
using NHapi.Base.Model;
using NHapi.Model.V24.Segment;

namespace gov.va.medora.mdo.dao.hl7.rxRefill
{
    public class RxRefillDecoder
    {

        public IList<Medication> parse(string message)
        {
            PipeParser pp = new PipeParser();
            ORP_O10 orp_o10 = (ORP_O10)pp.Parse(message);
            ORP_O10_ORCRXE orp_o10_orcrxe = new ORP_O10_ORCRXE(); // ORP_O10 doesn't parse the RXE segment of the response so we need this class extension

            if (!String.Equals(((MSA)orp_o10.GetStructure("MSA")).AcknowledgementCode.Value, "AA", StringComparison.CurrentCultureIgnoreCase))
            {
                throw new mdo.exceptions.MdoException("Error in refill response: " + message);
            }

            IList<Medication> meds = new List<Medication>();
            //IStructure[] structs = orp_o10.GetAll("RXE");

            string[] lines = message.Split(new char[] { '\r' });
            IList<string> rxeLines = new List<string>();
            foreach (string line in lines)
            {
                if (String.IsNullOrEmpty(line))
                {
                    continue;
                }
                if (line.StartsWith("RXE"))
                {
                    rxeLines.Add(line);
                }
            }

            for (int i = 0; i < rxeLines.Count; i++)
            {
                meds.Add(buildMedication(getRxe(rxeLines[i], orp_o10_orcrxe.getRxe(i))));
            }

            return meds;
        }

        Medication buildMedication(RXE rxe)
        {
            Medication med = new Medication();

            med.RxNumber = rxe.PrescriptionNumber.Value;
            med.Quantity = rxe.QuantityTiming.Quantity.Quantity.Value;
            med.StartDate = rxe.GiveCode.Identifier.Value;
            med.Status = rxe.GiveCode.Text.Value;
            med.Refills = rxe.NumberOfRefills.Value;

            return med;
        }

        RXE getRxe(string segment, RXE rxe)
        {
            string[] flds = segment.Split(new char[] { '|' });
            if (flds == null || flds.Length == 0 || flds.Length < 16)
            {
                return rxe;
            }

            rxe.PrescriptionNumber.Value = flds[15];
            rxe.QuantityTiming.Quantity.Quantity.Value = flds[5];
            rxe.GiveCode.Identifier.Value = flds[1].Split(new char[] { '^' })[3];
            rxe.GiveCode.Text.Value = flds[2].Split(new char[] { '^' })[0];
            rxe.NumberOfRefills.Value = flds[3];

            return rxe;
        }
    }
}
